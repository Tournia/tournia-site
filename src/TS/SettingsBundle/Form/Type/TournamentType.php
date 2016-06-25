<?php

namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TS\SiteBundle\Form\DataTransformer\StatusToNumberTransformer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;

class TournamentType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);
        $builder->add('url', TextType::class, array(
            'label' => 'tournamentType.url.label',
            'constraints' => array(
                new Assert\Regex(array(
                    'pattern' => '/^[a-zA-Z\-\_\d]+$/',
                    'match'   => true,
                    'message' => 'tournamentUrl.regex',
                )),
                new Assert\NotBlank(array(
                    'message' => 'tournamentUrl.notblank',
                ))
            ),
            'attr' => array("info"=>"tournamentType.url.info")
        ));
        $builder->add('emailFrom', EmailType::class, array(
        	'label' => 'tournamentType.emailFrom.label',
            'constraints' => new Email(
                array("checkMX" => true)
            ),
        ));
        $builder->add('contactName', TextType::class, array(
        	'label' => 'tournamentType.contactName.label',
        ));
        $builder->add('organizationEmailOnChange', TextType::class, array(
            'label' => 'tournamentType.organizationEmailOnChange.label',
            'required' => false,
            'constraints' => new Email(array("checkMX" => true)),
            'attr' => array("info"=>"tournamentType.organizationEmailOnChange.info")
        ));
        $builder->add('registrationGroupEnabled', CheckboxType::class, array(
            'label' => 'tournamentType.registrationGroupEnabled.label',
            'required' => false,
            'attr' => array("info"=>"tournamentType.registrationGroupEnabled.info")
        ));
        $builder->add('registrationGroupRequired', CheckboxType::class, array(
            'label' => 'tournamentType.registrationGroupRequired.label',
            'required' => false,
            'attr' => array("info"=>"tournamentType.registrationGroupRequired.info")
        ));
        $transformer = new StatusToNumberTransformer($options['tournament']);
	    $builder->add(
        	$builder->create('newPlayerStatus', ChoiceType::class, array(
	        	'label' => 'tournamentType.newPlayerStatus.label',
	        	'choices' => $options['tournament']->getStatusOptions(),
	        	'required' => false,
	        	'empty_value' => false
	        ))->addModelTransformer($transformer)
	    );
        $builder->add('statusOptions', CollectionType::class, array(
        	'type' => 'text',
        	'options' => array(
        		'required' => true,
        		'label' => 'tournamentType.statusOptions.options.label',
                'constraints' => new NotBlank(),
        	),
        	'label' => 'tournamentType.statusOptions.label',
        	'allow_add' => true,
        	'allow_delete' => true,
        ));
        $builder->add('registrationFormFields', CollectionType::class, array(
        	'type' => new RegistrationFormFieldType(),
        	'options' => array(
        		'required' => true,
        		'label' => 'tournamentType.registrationFormFields.options.label',
        	),
        	'label' => 'tournamentType.registrationFormFields.label',
        	'allow_add' => true,
        	'allow_delete' => true,
        ));
        $builder->add('nrSets', IntegerType::class, array(
        	'label' => 'tournamentType.nrSets.label',
        	'required' => false,
        ));
        $builder->add('startDateTime', DateTimeType::class, array(
            'label' => 'tournamentType.startDateTime.label',
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy',
            'attr' => array("info"=>"tournamentType.startDateTime.info")
        ));
        $builder->add('endDateTime', DateTimeType::class, array(
            'label' => 'tournamentType.endDateTime.label',
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy',
            'attr' => array("info"=>"tournamentType.endDateTime.info")
        ));
        $builder->add('checkScoreMin', IntegerType::class, array(
            'label' => 'tournamentType.checkScoreMin.label',
            'attr' => array("info"=>"tournamentType.checkScoreMin.info"),
            'required' => false,
        ));
        $builder->add('checkScoreMax', IntegerType::class, array(
            'label' => 'tournamentType.checkScoreMax.label',
            'attr' => array("info"=>"tournamentType.checkScoreMax.info"),
            'required' => false,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\Tournament',
            'translation_domain' => 'settings',
        ));
        
        $resolver->setRequired(array(
            'em',
            'tournament'
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
            'tournament' => 'TS\ApiBundle\Entity\Tournament'
        ));
    }
}
