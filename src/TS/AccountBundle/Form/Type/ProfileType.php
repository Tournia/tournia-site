<?php
namespace TS\AccountBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'profileType.name.label',
        ));
        $builder->add('email', EmailType::class, array(
            'label' => 'profileType.email.label',
            'attr' => array('info' => 'profileType.email.info'),
            'constraints' => array(
                new NotBlank(array('message' => 'profileType.email.notBlank')),
            ),
        ));

        $builder->add('firstName', TextType::class, array(
            'label' => 'profileType.firstName.label',
            'required' => false,
        ));
        $builder->add('lastName', TextType::class, array(
            'label' => 'profileType.lastName.label',
            'required' => false,
        ));
        $builder->add('gender', ChoiceType::class, array(
            'label' => 'profileType.gender.label',
            'choices' => array(
                'M' => 'profileType.gender.male',
                'F' => 'profileType.gender.female'
            ), 
            'required' => true,
            'expanded' => true
        ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\Person',
            'translation_domain' => 'profile',
        ));
    }
}

