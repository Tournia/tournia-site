<?php

namespace TS\ControlBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TS\SiteBundle\Form\DataTransformer\MultipleStatusToNumberTransformer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class OrganizerEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['tournament']->getRegistrationGroupEnabled()) {
            $builder->add('contactPlayers', ChoiceType::class, array(
            	'choices'   => array(
                    'onlyContactPlayers' => 'organizerEmailType.contactPlayers.choices.onlyContactPlayers',
                    'allPlayers' => 'organizerEmailType.contactPlayers.choices.allPlayers'
                ),
            	'label' => 'organizerEmailType.contactPlayers.label',
            	'required' => true,
            	'expanded' => true,
            	'mapped' => false,
            	'constraints' => new NotNull(array('message' => 'organizerEmailType.contactPlayers.notnull')),
            ));
        }
        $transformer = new MultipleStatusToNumberTransformer($options['tournament']);
        $builder->add(
        	$builder->create('status', ChoiceType::class, array(
	        	'choices'   => $options['tournament']->getStatusOptions(),
	        	'label' => 'organizerEmailType.status.label',
	        	'required' => true,
	        	'expanded' => true,
	        	'multiple' => true,
	        	'mapped' => false,
	        	'constraints' => new NotNull(array('message' => 'organizerEmailType.status.notnull')),
	        ))->addModelTransformer($transformer)
	    );
	    
        $builder->add('subject', TextType::class, array(
        	'label' => 'organizerEmailType.subject.label',
        	'required' => true,
        	'mapped' => false,
        	'constraints' => new NotBlank(),
        ));
        $builder->add('message', TextareaType::class, array(
	        'label' => 'organizerEmailType.message.label',
        	'attr' => array('cols' => 100, 'rows' => 10),
        	'required' => false,
        	'mapped' => false,
        	'constraints' => new NotBlank(),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'control',
        ));
        
        $resolver->setRequired(array(
            'tournament'
        ));

        $resolver->setAllowedTypes(array(
            'tournament' => 'TS\ApiBundle\Entity\Tournament',
        ));
    }
}