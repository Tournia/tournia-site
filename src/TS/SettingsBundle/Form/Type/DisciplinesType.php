<?php

namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DisciplinesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('disciplineTypes', CollectionType::class, array(
            'type' => new DisciplineTypeType(),
            'options' => array(
                'required' => true,
                'label' => 'disciplinesType.disciplineTypes.label',
            ),
            'label' => 'disciplinetmp',
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => new Assert\Count(array(
                    'min' => 1,
                    'minMessage' => 'disciplinesType.disciplineTypes.notempty',
            )),
        ));

        $disciplineType = new DisciplineType();
        $disciplineType->setTournament($options['tournament']);
        $builder->add('disciplines', CollectionType::class, array(
            'type' => $disciplineType,
            'options' => array(
                'required' => true,
                'label' => 'disciplinesType.disciplines.label',
            ),
            'label' => 'disciplinetmp',
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => new Assert\Count(array(
                'min' => 1,
                'minMessage' => 'disciplinesType.disciplines.notempty',
            )),
        ));

        $maxRegistrationDisciplinesChoices = array(
            0 => 'tournamentType.maxRegistrationDisciplines.noLimit',
        );
        for ($i = 1; $i <= count($options['tournament']->getDisciplineTypes()); $i++) {
            $maxRegistrationDisciplinesChoices[$i] = $i;
        }
        $builder->add('maxRegistrationDisciplines', ChoiceType::class, array(
            'label' => 'tournamentType.maxRegistrationDisciplines.label',
            'attr' => array("formComment"=>"tournamentType.maxRegistrationDisciplines.formComment"),
            'choices' => $maxRegistrationDisciplinesChoices,
            'expanded' => false,
            'multiple' => false,
            'placeholder' => false,
            'required' => true,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\Tournament',
            'translation_domain' => 'settings',
        ));

        $resolver->setRequired(array(
            'tournament'
        ));

        $resolver->setAllowedTypes(array(
            'tournament' => 'TS\ApiBundle\Entity\Tournament'
        ));
    }
}
