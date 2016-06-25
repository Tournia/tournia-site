<?php
namespace TS\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PayOutAdjustmentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('label', TextType::class, array(
            'label' => 'Label',
            'required' => true,
            'constraints' => new NotBlank(),
        ));
        $builder->add('quantity', IntegerType::class, array(
            'label' => 'Quantity',
            'required' => true,
            'constraints' => new NotBlank(),
        ));
        $builder->add('amount', IntegerType::class, array(
            'label' => 'Total amount (in cents)',
            'required' => true,
            'constraints' => new NotBlank(),
            'attr' => array('class' => 'adjustmentAmount')
        ));
    }

    public function getName()
    {
        return 'payoutAdjustment';
    }
}