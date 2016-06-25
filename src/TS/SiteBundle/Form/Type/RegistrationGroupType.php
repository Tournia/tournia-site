<?php

namespace TS\SiteBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class RegistrationGroupType extends AbstractType
{
	private $groupEntity;
	
	public function __construct($groupEntity) {
    	$this->groupEntity = $groupEntity;
    }	
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);
        $builder->add('country', TextType::class);
        
        if ($this->groupEntity != null) { // not a new group
        	$groupId = $this->groupEntity->getId();
	        $builder->add('contactPlayers', EntityType::class, array (
		        "class" => "TSApiBundle:Player",
		        "expanded" => true,
		        "multiple" => true,
		        "property" => "name",
		        'query_builder' => function(EntityRepository $er) use ($groupId) {
			        return $er->createQueryBuilder('p')
			        	->leftJoin('p.registrationGroup', 'registrationGroup')
			        	->where('registrationGroup.id = :groupId')
			        	->setParameter('groupId', $groupId)
			        	->orderBy('p.firstName', 'ASC');
			        	
			    },
			    'mapped' => false,
			    'label' => 'registrationgroup.contact.label',
		    ));
		}
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\RegistrationGroup',
			'translation_domain' => 'site'
        ));
    }
}
