<?php

namespace TS\SettingsBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SiteType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('locationAddress', TextareaType::class, array(
        	'label' => 'sitePageType.locationAddress.label',
        ));
        $builder->add('htmlTitle', TextType::class, array(
            'label' => 'sitePageType.htmlTitle.label',
            'required' => false,
            'attr' => array("info"=>"sitePageType.htmlTitle.info")
        ));
        $builder->add('htmlSubtitle', TextType::class, array(
            'label' => 'sitePageType.htmlSubtitle.label',
            'required' => false,
            'attr' => array("info"=>"sitePageType.htmlSubtitle.info")
        ));
        $builder->add('upload', FileType::class, array(
            'label' => 'sitePageType.upload.label',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('sitePages', CollectionType::class, array(
            'type' => new SitePageType(),
            'options' => array(
                'required' => true,
                'label' => 'sitePageType.sitePages.options.label',
            ),
            'label' => 'sitePageType.sitePages.label',
            'allow_add' => true,
            'allow_delete' => true,
        ));
        $builder->add('metaKeywords', TextType::class, array(
            'label' => 'sitePageType.metaKeywords.label',
            'required' => false,
            'attr' => array("info"=>"sitePageType.metaKeywords.info")
        ));
        $builder->add('metaDescription', TextType::class, array(
            'label' => 'sitePageType.metaDescription.label',
            'required' => false,
            'attr' => array("info"=>"sitePageType.metaDescription.info")
        ));

        $builder->add('isPublished', CheckboxType::class, array(
            'label' => 'sitePageType.isPublished.label',
            'required' => false,
            'attr' => array("info"=>"sitePageType.isPublished.info")
        ));

        // file (image) selection
        $frontImageOptions = array(
            'empty_value' => 'sitePageType.frontImage.emptyvalue',
        );
        $builder->add('frontImage', EntityType::class, array_merge_recursive($this->getFileDefaultOptions($options['site'], false), $frontImageOptions));

        $headerBackgroundImageOptions = array(
            'empty_value' => 'sitePageType.headerBackgroundImage.emptyvalue',
        );
        $builder->add('headerBackgroundImage', EntityType::class, array_merge_recursive($this->getFileDefaultOptions($options['site'], 'sample'), $headerBackgroundImageOptions));

        $infoBlockImageOptions = array(
            'empty_value' => 'sitePageType.infoBlockImage.emptyvalue',
        );
        $builder->add('infoBlockImage', EntityType::class, array_merge_recursive($this->getFileDefaultOptions($options['site'], false), $infoBlockImageOptions));

        $facebookImageOptions = array(
            'empty_value' => 'sitePageType.facebookImage.emptyvalue',
        );
        $builder->add('facebookImage', EntityType::class, array_merge_recursive($this->getFileDefaultOptions($options['site'], false), $facebookImageOptions));

        $builder->add('editImage', ChoiceType::class, array(
            'mapped' => false,
            'choices'  => array(
                'frontImage' => 'sitePageType.editImage.frontImage',
                'headerBackgroundImage' => 'sitePageType.editImage.headerBackgroundImage',
                'infoBlockImage' => 'sitePageType.editImage.infoBlockImage',
                'facebookImage' => 'sitePageType.editImage.facebookImage',
            ),
            'required' => false,
            'multiple' => false,
            'expanded' => false,
            'placeholder' => false,
        ));

        $positionDefaultOptions = array(
            'choices'  => array(
                'left top' => 'sitePageType.position.lefttop',
                'left center' => 'sitePageType.position.leftcenter',
                'left bottom' => 'sitePageType.position.leftbottom',
                'center top' => 'sitePageType.position.centertop',
                'center center' => 'sitePageType.position.centercenter',
                'center bottom' => 'sitePageType.position.centerbottom',
                'right top' => 'sitePageType.position.righttop',
                'right center' => 'sitePageType.position.rightcenter',
                'right bottom' => 'sitePageType.position.rightbottom',
            ),
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'placeholder' => false,
            'label' => 'sitePageType.position.label'
        );

        $builder->add('frontImagePosition', ChoiceType::class, $positionDefaultOptions);
        $builder->add('headerBackgroundImagePosition', ChoiceType::class, $positionDefaultOptions);
    }

    private function getFileDefaultOptions($site, $specialType) {
        $fileDefaultOptions = array(
            'class' => 'TSSiteBundle:File',
            'query_builder' => function(EntityRepository $er) use ($site, $specialType) {
                $query = $er->createQueryBuilder('f')
                    ->leftJoin('f.site', 'site')
                    ->setParameter('site', $site);

                if ($specialType === false) {
                    $query = $query
                        ->where('site = :site')
                        ->orderBy('f.fileName', 'ASC');
                } else {
                    $query = $query
                        ->where('site = :site OR f.specialType = :specialType')
                        ->setParameter('specialType', $specialType)
                        ->orderBy('f.specialType', 'ASC')
                        ->orderBy('f.fileName', 'ASC');
                }
                return $query;
            },
            'property' => 'configName',
            'required' => false,
            'empty_data' => null,
            'mapped' => true,
            'label' => 'sitePageType.image.label',
        );
        return $fileDefaultOptions;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\SiteBundle\Entity\Site',
            'translation_domain' => 'settings',
        ));
        
        $resolver->setRequired(array(
            'em',
            'site'
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
            'site' => 'TS\SiteBundle\Entity\Site'
        ));
    }
}
