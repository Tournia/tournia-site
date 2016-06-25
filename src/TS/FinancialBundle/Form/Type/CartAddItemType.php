<?php

namespace TS\FinancialBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotNull;

class CartAddItemType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tournament = $options['tournament']; /* @var \TS\ApiBundle\Entity\Tournament $tournament */
        $tournamentId = $tournament->getId();
        // add payment functionality
        $builder->add('player', EntityType::class, array(
            'class' => 'TSApiBundle:Player',
            'property' => 'name',
            'mapped' => false,
            'query_builder' => function(EntityRepository $er) use ($tournamentId) {
                    return $er->createQueryBuilder('p')
                        ->where('p.tournament = :tournamentId')
                        ->setParameter('tournamentId', $tournamentId)
                        ->orderBy('p.firstName', 'ASC');
                },
            'multiple' => false,
            'expanded' => false,
            'label' => 'cartAddType.player.label',
            'required' => true,
            'placeholder' => 'cartAddType.player.placeholder',
            'constraints' => new NotNull(),
        ));

        $builder->add('addProduct', EntityType::class, array(
            'class' => 'TSFinancialBundle:Product',
            'property' => 'name',
            'mapped' => false,
            'query_builder' => function(EntityRepository $er) use ($tournamentId) {
                    return $er->createQueryBuilder('p')
                        ->where('p.tournament = :tournamentId AND p.isHidden = false')
                        ->setParameter('tournamentId', $tournamentId)
                        ->orderBy('p.position', 'ASC');
                },
            'multiple' => true,
            'expanded' => true,
            'label' => 'cartAddType.addProduct.label',
            'required' => true,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'financial'
        ));

        $resolver->setRequired(array(
            'em',
            'tournament'
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
            'tournament' => 'TS\ApiBundle\Entity\Tournament',
        ));
    }
}

