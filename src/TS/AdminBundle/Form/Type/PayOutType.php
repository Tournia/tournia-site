<?php
namespace TS\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Validator\Constraints\Email;

class PayOutType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('paypalAccount', EmailType::class, array(
            'label' => 'PayPal account',
            'required' => false,
            'constraints' => new Email(array(
                'message' => 'The email "{{ value }}" is not a valid email address.',
                'checkMX' => true,
            )),
        ));
        $builder->add('bankAccount', TextType::class, array(
            'label' => 'Bank Account',
            'required' => false,
        ));
        $builder->add('dateTime', DateTimeType::class, array(
            'label' => 'DateTime',
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy HH:mm',
        ));
        $builder->add('adjustments', CollectionType::class, array(
            'type' => new PayOutAdjustmentType(),
            'options' => array(
                'required' => true,
                'label' => 'Adjustment',
            ),
            'label' => 'Additional payments',
            'allow_add' => true,
            'allow_delete' => true,
            'mapped' => false,
        ));

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\FinancialBundle\Entity\PayOut',
        ));

        $resolver->setRequired(array(
            'financialModel',
        ));

        $resolver->setAllowedTypes(array(
            'financialModel' => 'TS\FinancialBundle\Model\FinancialModel',
        ));
    }
}