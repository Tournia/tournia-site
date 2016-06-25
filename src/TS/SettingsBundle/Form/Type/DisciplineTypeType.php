<?php
namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Type;

class DisciplineTypeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'disciplineTypeType.name.label',
            'required' => true,
            'constraints' => new NotBlank(),
        ));
        $builder->add('partnerRegistration', CheckboxType::class, array(
            'label' => 'disciplineTypeType.partnerRegistration.label',
            'required' => false,
            'attr' => array("info"=>"disciplineTypeType.partnerRegistration.info")
        ));
        $builder->add('position', HiddenType::class, array());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\DisciplineType',
            'translation_domain' => 'settings',
        ));
    }
}