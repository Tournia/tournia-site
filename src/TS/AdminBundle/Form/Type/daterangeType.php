<?php
namespace TS\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Validator\Constraints\Email;

class DaterangeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDateTime', DateTimeType::class, array(
            'label' => 'Start date',
            'mapped' => false,
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy',
            'data' => new \DateTime('3 months ago'),
        ));

        $builder->add('endDateTime', DateTimeType::class, array(
            'label' => 'Start date',
            'mapped' => false,
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy',
            'data' => new \DateTime('now'),
        ));

    }
}