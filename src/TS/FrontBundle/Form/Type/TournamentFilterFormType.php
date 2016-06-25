<?php
namespace TS\FrontBundle\Form\Type;

use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;

class TournamentFilterFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('keyword', TextType::class, array(
            'label' => false,
            'required' => false
        ));

        $builder->add('startDate', TextType::class, array(
            'label' => false,
            'required' => false,
            'data' => $this->getDefaultFilter()['startDate']
        ));

        $builder->add('endDate', TextType::class, array(
            'label' => false,
            'required' => false,
            'data' => $this->getDefaultFilter()['endDate']
        ));

        $builder->add('location', TextType::class, array(
            'label' => false,
            'required' => false,
        ));

        $builder->add('limit', HiddenType::class, array(
            'label' => false,
            'data' => $this->getDefaultFilter()['limit'],
            'error_bubbling' => false,
            'constraints' =>
                array(
                    new Choice(array(
                        'choices' => $this->getAllowedNumberOfResults(),
                    ))
                ),
        ));
    }

    public function getDefaultFilter()
    {
        return array(
            'startDate' => null, //(new DateTime('today'))->format('d-m-Y')
            'endDate' => null,
            'limit' => $this->getAllowedNumberOfResults()[0]
        );
    }

    public function getAllowedNumberOfResults() {
        return array(20, 50, 100);
    }
}

