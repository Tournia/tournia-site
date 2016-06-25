<?php
namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SitePageType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, array(
        	'label' => 'sitePageType.title.label',
        	'required' => true,
            'attr' => array("info"=>"sitePageType.title.info"),
            'constraints' => new NotBlank(),
    	));
    	$builder->add('url', TextType::class, array(
        	'label' => 'sitePageType.url.label',
        	'required' => false,
            'attr' => array("info"=>"sitePageType.url.info"),
            'constraints' => new NotBlank(),
    	));
        $builder->add('showInfoBlock', CheckboxType::class, array(
            'label' => 'sitePageType.showInfoBlock.label',
            'required' => false,
            'attr' => array("info"=>"sitePageType.showInfoBlock.info")
        ));
    	$builder->add('html', TextareaType::class, array(
            'label' => 'sitePageType.html.label',
            'attr' => array('class' => 'ckeditor'),
            'required' => true,
            'constraints' => new NotBlank(),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\SiteBundle\Entity\SitePage',
            'translation_domain' => 'settings',
        ));
    }
}