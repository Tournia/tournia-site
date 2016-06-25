<?php
namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RegistrationFormFieldType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
        	'label' => 'registrationFormFieldType.name.label',
        	'required' => true,
            'constraints' => new NotBlank(),
    	));
    	$builder->add('infoText', TextType::class, array(
        	'label' => 'registrationFormFieldType.infoText.label',
        	'required' => false
    	));
        $builder->add('formComment', TextType::class, array(
            'label' => 'registrationFormFieldType.formComment.label',
            'required' => false
        ));
    	$builder->add('isRequired', CheckboxType::class, array(
        	'label' => 'registrationFormFieldType.isRequired.label',
        	'required' => false
    	));
        $builder->add('isHidden', CheckboxType::class, array(
            'label' => 'registrationFormFieldType.isHidden.label',
            'required' => false,
            'attr' => array("info"=>"registrationFormFieldType.isHidden.info"),
        ));
    	$builder->add('type', ChoiceType::class, array(
        	'label' => 'registrationFormFieldType.type.info',
        	'choices'   => array(
                'text' => 'registrationFormFieldType.type.choices.text',
                'textarea' => 'registrationFormFieldType.type.choices.textarea',
                'checkbox' => 'registrationFormFieldType.type.choices.checkbox',
                'choice' => 'registrationFormFieldType.type.choices.choice'
            ),
        	'required' => false,
        	'empty_value' => false
    	));
        $builder->add('choiceExpanded', ChoiceType::class, array(
            'label' => 'registrationFormFieldType.choiceExpanded.label',
            'choices'   => array(true => 'registrationFormFieldType.choiceExpanded.choices.dropdown', false => 'registrationFormFieldType.choiceExpanded.choices.radio'),
            'data' => true,
            'required' => false,
            'expanded' => true,
            'multiple' => false,
        ));
    	$builder->add('choiceOptions', CollectionType::class, array(
        	'type' => 'text',
        	'options' => array(
        		'required' => true,
        		'label' => 'registrationFormFieldType.choiceOptions.name',
        	),
        	'label' => 'registrationFormFieldType.choiceOptions.label',
        	'allow_add' => true,
        	'allow_delete' => true,
        ));
        $builder->add('position', HiddenType::class, array());
        
        // choiceExpanded is by default not set to false
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $field = $event->getData();
            $form = $event->getForm();

            $formOptions = array(
                'label' => 'registrationFormFieldType.formOptions.label',
                'choices'   => array(false => 'registrationFormFieldType.formOptions.choices.dropdown', true => 'registrationFormFieldType.formOptions.choices.radio'),
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'empty_value' => false,
            );

            if (!$field) {
                $formOptions['data'] = false;
            }

            $form->add('choiceExpanded', ChoiceType::class, $formOptions);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\RegistrationFormField',
            'translation_domain' => 'settings',
        ));
    }
}