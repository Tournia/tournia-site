<?php

namespace TS\SiteBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use TS\SiteBundle\Form\DataTransformer\RegistrationGroupToNumberTransformer;
use TS\SiteBundle\Form\DataTransformer\StatusToNumberTransformer;
use TS\SiteBundle\Form\DataTransformer\ChoiceToIndexTransformer;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\IsTrue;

class PlayerType extends AbstractType
{
    private $tournament;
    private $securityContext;
    private $registrationForValue;
    private $registrationEmailValue;
    
    public function __construct(\TS\ApiBundle\Entity\Tournament $tournament, $securityContext, $registrationForValue, $registrationEmailValue) {
    	$this->tournament = $tournament;
    	$this->securityContext = $securityContext;
    	$this->registrationForValue = $registrationForValue;
    	$this->registrationEmailValue = $registrationEmailValue;
    }
    
    private function getGroupList() {
    	$groups = $this->tournament->getRegistrationGroups();
        $groupIds = array();
        $groupNames = array();
        foreach ($groups as &$group) {
        	$groupIds[] = $group->getId();
        	$groupNames[] = $group->getName();
        }
        return new ChoiceList($groupIds, $groupNames);
    	
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('registrationFor', ChoiceType::class, array(
        	'choices' => array(
        		'me' => 'playerType.registrationFor.me',
        		'else' => 'playerType.registrationFor.else',
        	), 
        	'required' => true,
        	'expanded' => true,
        	'empty_value' => false,
        	'label' => 'playerType.registrationFor.label',
        	'mapped' => false,
        	'data' => $this->registrationForValue
        ));
        $builder->add('registrationEmail', EmailType::class, array(
        	'required' => true,
        	'label' => 'playerType.registrationEmail.email',
        	'attr' => array('info' => 'playerType.registrationEmail.info'),
        	'mapped' => false,
        	'data' => $this->registrationEmailValue,
        	'constraints' =>
                array(
                    new Email(array(
                        'message' => 'playerType.registrationEmail.notvalid',
                        'checkMX' => true,
                    )),
                    new NotBlank(array(
                        'message' => 'playerType.registrationEmail.notblank'
                    ))
                ),
        ));
        
        if ($options['tournament']->getRegistrationGroupEnabled()) {
            $registrationGroupOptions = array(
                'choice_list' => $this->getGroupList(),
                'empty_value' => 'playerType.group.select',
                'mapped' => true,
                'label' => 'playerType.group.label',
            );
            if ($options['tournament']->getRegistrationGroupRequired()) {
                $registrationGroupOptions['constraints'] = new NotBlank(
                    array('message' => 'playerType.group.notblank')
                );
                $registrationGroupOptions['required'] = true;
            } else {
                $registrationGroupOptions['empty_value'] = 'playerType.group.emptyvalue';
                $registrationGroupOptions['required'] = false;
            }
            $transformer = new RegistrationGroupToNumberTransformer($options['em']);
        	$builderInterface = $builder->create('registrationGroup', 'choice', $registrationGroupOptions)->addModelTransformer($transformer);
            $builder->add($builderInterface);
        }
        $builder->add('firstName', TextType::class, array(
        	'label'=>'playerType.firstName.label',
        	'attr' => array('placeholder' => 'playerType.firstName.placeholder')
        ));
        $builder->add('lastName', TextType::class, array(
        	'label'=>'playerType.lastName.label',
        	'attr' => array('placeholder' => 'playerType.lastName.placeholder')
        ));
        $builder->add('gender', ChoiceType::class, array(
            'label'=>'playerType.gender.label',
            'choices' => array(
        		'M' => 'playerType.gender.male',
        		'F' => 'playerType.gender.female'
        	), 
        	'required' => true,
        	'expanded' => true
        ));

        $tournamentId = $this->tournament->getId();
        foreach ($options['tournament']->getDisciplineTypes() as $disciplineType) {
            /* @var \TS\ApiBundle\Entity\DisciplineType $disciplineType */

            // find DisciplinePlayer to be able to set data
            $disciplinePlayer = null;
            foreach ($options['player']->getDisciplinePlayers() as $dp) {
                if ($dp->getDiscipline()->getDisciplineType() == $disciplineType) {
                    $disciplinePlayer = $dp;
                }
            }
            $builder->add('discipline-'. $disciplineType->getId(), EntityType::class, array(
                'class' => 'TSApiBundle:Discipline',
                'property' => 'name',
                'query_builder' => function(EntityRepository $er) use ($disciplineType, $tournamentId) {
                    return $er->createQueryBuilder('d')
                        ->where('d.tournament = :tournamentId AND d.disciplineType = :disciplineType AND d.isHidden = false')
                        ->setParameter('disciplineType', $disciplineType)
                        ->setParameter('tournamentId', $tournamentId)
                        ->orderBy('d.position', 'ASC');
                },
                'multiple' => false,
                'expanded' => false,
                'label' => $disciplineType->getName(),
                'empty_value' => 'playerType.discipline.notPlaying',
                'required' => false,
                'mapped' => false,
                'data' => (is_null($disciplinePlayer) ? null : $disciplinePlayer->getDiscipline()),
            ));

            if ($disciplineType->getPartnerRegistration()) {
                $builder->add('disciplinePartner-'. $disciplineType->getId(), TextType::class, array(
                    'label'=>'playerType.disciplinePartner.label',
                    'required' => false,
                    'attr' => array('info' => 'playerType.disciplinePartner.info'),
                    'mapped' => false,
                    'data' => (is_null($disciplinePlayer) ? null : $disciplinePlayer->getPartner()),
                ));
            }
        }

        if ($this->securityContext->isGranted("EDIT", $this->tournament)) {
        	$transformer = new StatusToNumberTransformer($this->tournament);
	        $builder->add(
	        	$builder->create('status', ChoiceType::class, array(
		        	'choices' => $this->tournament->getStatusOptions(),
		        	'required' => true,
		        	'empty_value' => false)
		        )->addModelTransformer($transformer)
		    );

            // make notification optional for organizer
            $builder->add('sendPlayerNotification', CheckboxType::class, array(
                'label' => 'playerType.sendPlayerNotification.label',
                'data' => true,
                'required' => false,
                'mapped' => false,
            ));
	    }
	    
	    foreach ($options['player']->getRegistrationFormValues() as $formValue) {
	    	$field = $formValue->getField();
	    	$optionsArray = array();
	    	if (!is_null($formValue->getValue())) {
	    		if ($field->getType() == 'checkbox') {
	    			$optionsArray['data'] = $formValue->getValue() == 1; // save data as boolean
	    		} else {
	    			$optionsArray['data'] = $formValue->getValue();
	    		}
	    	}
	    	$optionsArray['mapped'] = false;
	    	if (!is_null($field->getName())) {
	    		$optionsArray['label'] = $field->getName();
	    	}
            $optionsArray['attr'] = array();
	    	if (!is_null($field->getInfoText())) {
	    		$optionsArray['attr']['info'] = $field->getInfoText();
	    	}
            if (!is_null($field->getFormComment())) {
                $optionsArray['attr']['formComment'] = $field->getFormComment();
            }
	    	if ($field->getIsRequired() === true) {
	    		$optionsArray['required'] = true;
	    		$optionsArray['constraints'] = new NotNull(array('message' => 'playerType.notempty'));
                $optionsArray['constraints'] = new NotBlank(array('message' => 'playerType.notempty'));
                if ($field->getType() == 'checkbox') {
                    $optionsArray['constraints'] = new IsTrue(array('message' => 'playerType.notchecked'));
                }
	    	} else {
	    		$optionsArray['required'] = false;
	    	}
	    	
	    	if ($field->getType() == 'choice') {
	    		$optionsArray['choices'] = $field->getChoiceOptions();
	    		$optionsArray['expanded'] = $field->getChoiceExpanded() === true;
	    	}

            $typeClass = $field->getType();
            if ($field->getType() == "text") {
                $typeClass = TextType::class;
            } else if ($field->getType() == "textarea") {
                $typeClass = TextareaType::class;
            } else if ($field->getType() == "checkbox") {
                $typeClass = CheckboxType::class;
            } else if ($field->getType() == "choice") {
                $typeClass = ChoiceType::class;
            }
	    	
	    	$bInterface = $builder->create('formValue-'. $field->getId(), $typeClass, $optionsArray);
	    	if ($field->getType() == 'choice') {
	    		// add transformer in order for values to save instead of index number
	    		$transformer = new ChoiceToIndexTransformer($field->getChoiceOptions());
	    		$bInterface->addModelTransformer($transformer);
	    	}
	    	$builder->add($bInterface);
	    }

        // add payment functionality
        if ($this->tournament->getFinancialEnabled()) {
            $builder->add('addProduct', EntityType::class, array(
                'class' => 'TSFinancialBundle:Product',
                'property' => 'name',
                'mapped' => false,
                'query_builder' => function(EntityRepository $er) use ($tournamentId) {
                        return $er->createQueryBuilder('p')
                            ->where('p.tournament = :tournamentId AND p.isHidden = false')
                            ->setParameter('tournamentId', $tournamentId)
                            ->orderBy('p.position', 'ASC');
                    },
                'multiple' => true,
                'expanded' => true,
                'label' => 'playerType.addProduct.label',
                'required' => false,
            ));
        }

        $builder->add('conditions', CheckboxType::class, array(
            'required' => true,
            'mapped' => false,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\Player',
            'translation_domain' => 'site'
        ));
        
        $resolver->setRequired(array(
            'em',
            'player',
            'tournament'
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
            'player' => 'TS\ApiBundle\Entity\Player',
            'tournament' => 'TS\ApiBundle\Entity\Tournament',
        ));
    }
}

