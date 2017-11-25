<?php

namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TS\SiteBundle\Form\DataTransformer\StatusToNumberTransformer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;
use TS\SettingsBundle\Form\Type\FinancialProductType;

class FinancialType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // financial method
        $builder->add('financialMethod', ChoiceType::class, array(
            'choices' => array(
                'free' => 'financialType.financialMethod.free',
                'invoice' => 'financialType.financialMethod.invoice',
                'payments' => 'financialType.financialMethod.payments'
            ),
            'label' => 'financialType.financialMethod.label',
        ));
        $builder->add('paypalAccountUsername', TextType::class, array(
            'label' => 'financialType.paypalAccountUsername.label',
            'required' => false,
        ));
        $builder->add('paypalAccountPassword', TextType::class, array(
            'label' => 'financialType.paypalAccountPassword.label',
            'required' => false,
        ));
        $builder->add('paypalAccountSignature', TextType::class, array(
            'label' => 'financialType.paypalAccountSignature.label',
            'required' => false,
        ));

        $financialProductType = new FinancialProductType("TS\FinancialBundle\Entity\Product", array());
        $builder->add('products', CollectionType::class, array(
            'type' => $financialProductType,
            'options' => array(
                'required' => true,
                'label' => 'financialType.product.label',
            ),
            'label' => 'financialType.products.label',
            'allow_add' => true,
            'allow_delete' => true,
        ));

        $builder->add('paymentCurrency', ChoiceType::class, array(
            'choices' => array(
                'EUR' => 'financialType.paymentCurrency.currency.eur',
                'GBP' => 'financialType.paymentCurrency.currency.gbp',
                'NOK' => 'financialType.paymentCurrency.currency.nok',
                'USD' => 'financialType.paymentCurrency.currency.usd'
            ),
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'empty_value' => false,
            'label' => 'financialType.paymentCurrency.label',
        ));
        $builder->add('paymentUpdateStatus', CheckboxType::class, array(
            'label' => 'financialType.paymentUpdateStatus.label',
            'required' => false,
        ));
        $transformer = new StatusToNumberTransformer($options['tournament']);
        $builder->add(
            $builder->create('paymentUpdateFromStatus', ChoiceType::class, array(
                'label' => 'financialType.paymentUpdateFromStatus.label',
                'choices' => $options['tournament']->getStatusOptions(),
                'required' => false,
                'empty_value' => false
            ))->addModelTransformer($transformer)
        );
        $builder->add(
            $builder->create('paymentUpdateToStatus', ChoiceType::class, array(
                'label' => 'financialType.paymentUpdateToStatus.label',
                'choices' => $options['tournament']->getStatusOptions(),
                'required' => false,
                'empty_value' => false
            ))->addModelTransformer($transformer)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\Tournament',
            'translation_domain' => 'settings',
        ));

        $resolver->setRequired(array(
            'em',
            'tournament',
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
            'tournament' => 'TS\ApiBundle\Entity\Tournament',
        ));
    }
}
