<?php

namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class AuthorizationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choiceObjects = array(
            "createRegistrationChoice" => true,
            "changeRegistrationChoice" => true,
            "apiChoice" => true,
            "liveScoreChoice" => false,
            "live2ndCallChoice" => false,
        );

        foreach ($choiceObjects as $name=>$hasDateSpecific) {
            $choices = array(
                0 => 'authorizationType.choice.notAllowed',
                1 => 'authorizationType.choice.allowed',
            );
            if ($hasDateSpecific) {
                $choices[-1] = "authorizationType.choice.dateSpecific";
            }

            $builder->add($name, ChoiceType::class, array(
                'choices' => $choices,
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'empty_value' => false
            ));
        }

        $dateTimeObjects = array(
            "createRegistrationStart",
            "createRegistrationEnd",
            "changeRegistrationStart",
            "changeRegistrationEnd",
            "apiStart",
            "apiEnd",
        );

        foreach ($dateTimeObjects as $name) {
            $builder->add($name, DateTimeType::class, array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm'
            ));
        }

        $builder->add('livePassword', TextType::class, array(
            'required' => false,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\Authorization',
            'translation_domain' => 'settings',
        ));
    }
}
