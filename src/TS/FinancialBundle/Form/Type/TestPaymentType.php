<?php

namespace TS\FinancialBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TestPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('isDev', TextType::class, array('required' => false));
    }

    public function getName()
    {
        return 'test_payment';
    }
}