<?php
namespace TS\SettingsBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Type;

class DisciplineType extends AbstractType
{

    /**
     * @var \TS\ApiBundle\Entity\Tournament $tournament
     */
    private $tournament;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
        	'label' => 'disciplineType.name.label',
        	'required' => true,
            'constraints' => new NotBlank(),
    	));
    	$builder->add('gender', ChoiceType::class, array(
        	'choices' => array(
        		'M' => 'disciplineType.gender.male',
        		'F' => 'disciplineType.gender.female',
        		'B' => 'disciplineType.gender.both'
        	), 
        	'label' => 'disciplineType.gender.label',
        	'required' => true,
        	'expanded' => true,
        	'multiple' => false,
            'constraints' => new NotBlank(),
        ));
        $builder->add('disciplineType', EntityType::class, array(
            'class' => 'TSApiBundle:DisciplineType',
            'property' => 'name',
            'choices' => $this->tournament->getDisciplineTypes(),
            'label' => 'disciplineType.disciplineType.label',
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'constraints' => new NotBlank(),
        ));
        $builder->add('isHidden', CheckboxType::class, array(
            'label' => 'disciplineType.isHidden.label',
            'required' => false,
            'attr' => array("info"=>"disciplineType.isHidden.info"),
        ));
        $builder->add('position', HiddenType::class, array());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\Discipline',
            'translation_domain' => 'settings',
        ));
    }

    /**
     * Set Tournament
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     */
    public function setTournament($tournament) {
        $this->tournament = $tournament;
    }
}