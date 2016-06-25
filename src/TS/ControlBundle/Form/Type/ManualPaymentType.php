<?php

namespace TS\ControlBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TS\SiteBundle\Form\DataTransformer\PlayerToNumberTransformer;
use TS\ControlBundle\Form\DataTransformer\ProductToIdTransformer;
use Doctrine\ORM\EntityRepository;
use TS\ApiBundle\Entity\RegistrationGroup;

use TS\ApiBundle\Entity\Tournament;
use TS\ApiBundle\Entity\Player;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

class ManualPaymentType extends AbstractType
{ 
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', IntegerType::class);
        $builder->add('name', TextType::class, array(
            'label' => 'manualPaymentType.name.label',
        ));
        /*$tournament = $options['tournament'];
        $productTransformer = new ProductToIdTransformer($options['em']);
        $builder->add(
            $builder->create('name', EntityType::class, array(
                'class' => 'TSFinancialBundle:Product',
                'query_builder' => function(EntityRepository $er) use ($tournament) {
                    return $er->createQueryBuilder('p')
                        ->where('p.tournament = ?1')
                        ->setParameter(1, $tournament);
                },
                'expanded' => false,
                'multiple' => false,
                'property' => 'name',
                'empty_value' => 'Select a product',
                'mapped' => true,
            ))->addModelTransformer($productTransformer)
        );*/

        $builder->add('amount', IntegerType::class);
        $playerTransformer = new PlayerToNumberTransformer($options['em']);
        /*$tournament = $options['tournament'];
        $builder->add(
        	$builder->create('player', EntityType::class, array(
                'class' => 'TSApiBundle:Player',
                'query_builder' => function(EntityRepository $er) use ($tournament) {
                        return $er->createQueryBuilder('p')
                            ->where('p.tournament = ?1')
                            ->setParameter(1, $tournament);
                    },
                'expanded' => false,
                'multiple' => false,
                'property' => 'name',
                'empty_value' => 'Select a player',
                'mapped' => true,
        	))->addModelTransformer($playerTransformer)
        );*/
        $builder->add(
            $builder->create('player', ChoiceType::class, array(
                'label' => 'manualPaymentType.player.label',
                'choice_list' => $this->getPlayerList($options['tournament']),
                'empty_value' => 'manualPaymentType.player.emptyvalue',
                'mapped' => true
            ))->addModelTransformer($playerTransformer)
        );
    }

    private function getPlayerList($tournament) {
        $playerIds = array();
        $playerNames = array();
        foreach ($tournament->getPlayers() as $player) {
        	$playerIds[] = $player->getId();
        	$playerNames[] = $player->getName();
        }
        return new ChoiceList($playerIds, $playerNames);
    	
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\FinancialBundle\Entity\BoughtProduct',
            'translation_domain' => 'control',
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