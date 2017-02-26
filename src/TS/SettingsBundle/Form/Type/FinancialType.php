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
                //'payments' => 'financialType.financialMethod.payments'
            ),
            'label' => 'financialType.financialMethod.label',
        ));
        $builder->add('organizationPaysServiceFee', ChoiceType::class, array(
            'choices' => array(
                '0' => 'financialType.organizationPaysServiceFee.no',
                '1' => 'financialType.organizationPaysServiceFee.yes',
            ),
            'label' => 'financialType.organizationPaysServiceFee.label',
            'attr' => array(
                "formComment"=>"financialType.organizationPaysServiceFee.formComment"
            )
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
                'NOK' => 'financialType.paymentCurrency.currency.nok'
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
        $builder->add('financialPayoutBankAccount', TextType::class, array(
            'label' => 'financialType.financialPayoutBankAccount.label',
            'required' => false,
            'constraints' => new Assert\Iban(),
            'attr' => array("info"=>"financialType.financialPayoutBankAccount.info")
        ));
        $builder->add('financialPayoutPaypalEmail', EmailType::class, array(
            'label' => 'financialType.financialPayoutPaypalEmail.label',
            'required' => false,
            'constraints' => new Email(array("checkMX" => true)),
            'attr' => array(
                "info"=>"financialType.financialPayoutPaypalEmail.info",
                "formComment"=>"financialType.financialPayoutPaypalEmail.formComment"
            )
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
            'tournament',
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
            'tournament' => 'TS\ApiBundle\Entity\Tournament',
        ));
    }
}
