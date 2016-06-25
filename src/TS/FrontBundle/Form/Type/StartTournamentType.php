<?php
namespace TS\FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\NotBlank;

class StartTournamentType extends AbstractType
{

   /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => false, 
            'attr' => array(
                'mapped' => false,
                'placeholder' => 'startTournamentType.name.placeholder',
            ),
            'constraints' => array(
                new NotBlank(array('message' => 'startTournamentType.name.notblank')),
            ),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'front',
        ));
    }
}