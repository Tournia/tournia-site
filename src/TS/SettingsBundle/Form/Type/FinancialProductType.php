<?php
namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use Sylius\Bundle\ProductBundle\Form\Type\ProductType as BaseProductType;

class FinancialProductType extends BaseProductType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options); // Add default fields.

        $builder->remove('properties');
        $builder->remove('metaKeywords');
        $builder->remove('metaDescription');
        $builder->remove('availableOn');
        $builder->remove('name');
        $builder->remove('description');

        $builder->add('name', TextType::class, array(
            'label' => 'financialProductType.name.label',
            'required' => true,
            'constraints' => new NotBlank(),
        ));
        $builder->add('description', TextareaType::class, array(
            'label' => 'financialProductType.description.label',
            'required' => true,
            'constraints' => new NotBlank(),
        ));
        $builder->add('price', MoneyType::class, array(
            'grouping' => true,
            'divisor' => 100,
            'currency' => false,
            'constraints' => new NotBlank(),
        ));
        $builder->add('isHidden', CheckboxType::class, array(
            'label' => 'financialProductType.isHidden.label',
            'required' => false,
            'attr' => array("info"=>"financialProductType.isHidden.info"),
        ));
        $builder->add('initiallySelected', CheckboxType::class, array(
            'label' => 'financialProductType.initiallySelected.label',
            'required' => false,
            'attr' => array("info"=>"financialProductType.initiallySelected.info"),
        ));

        $builder->add('position', HiddenType::class, array());

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\FinancialBundle\Entity\Product',
            'translation_domain' => 'settings',
        ));
    }
}