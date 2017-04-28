<?php
namespace TS\SettingsBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;


class CreateTournamentType extends AbstractType
{

   /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (sizeof($options['person']->getOrganizingTournaments()) > 0) {
            $builder->add('copyTournament', EntityType::class, array(
                'label' => 'createTournamentType.copyTournament.label',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'mapped' => false,
                'choices' => $options['person']->getOrganizingTournaments(),
                'class' => 'TSApiBundle:Tournament',
                'property' => 'name',
                'placeholder' => 'createTournamentType.copyTournament.placeholder',
                'attr' => array(
                    'formComment' => "createTournamentType.copyTournament.formComment",
                )
            ));
        }
        $builder->add('name', TextType::class, array(
            'label' => 'createTournamentType.name.label',
            'attr' => array(
                'placeholder' => 'createTournamentType.name.placeholder'
            ),
        ));
        $builder->add('url', TextType::class, array(
            'label' => 'createTournamentType.tournamentUrl.label',
            'attr' => array(
                'placeholder' => 'createTournamentType.tournamentUrl.placeholder',
                "info" => "createTournamentType.tournamentUrl.info",
            ),
            'constraints' => array(
                new Assert\Regex(array(
                    'pattern' => '/^[a-zA-Z\-\_\d]+$/',
                    'match'   => true,
                    'message' => 'tournamentUrl.regex',
                )),
                new Assert\NotBlank(array(
                    'message' => 'tournamentUrl.notblank',
                )),
            ),
        ));
        $builder->add('locationAddress', TextareaType::class, array(
            'label' => 'sitePageType.locationAddress.label',
            'mapped' => false,
            'attr' => array("info"=>"createTournamentType.locationAddress.info")
        ));
        $builder->add('startDateTime', DateTimeType::class, array(
            'label' => 'tournamentType.startDateTime.label',
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy',
            'attr' => array("info"=>"tournamentType.startDateTime.info")
        ));
        $builder->add('endDateTime', DateTimeType::class, array(
            'label' => 'tournamentType.endDateTime.label',
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy',
            'attr' => array("info"=>"tournamentType.endDateTime.info")
        ));
        $builder->add('contactName', TextType::class, array(
            'label' => 'createTournamentType.contactName.label',
            'attr' => array("info"=>"createTournamentType.contactName.info"),
        ));
        $builder->add('emailFrom', EmailType::class, array(
            'label' => 'createTournamentType.emailFrom.label',
            'attr' => array("info"=>"createTournamentType.emailFrom.info"),
        ));

        // Sport
        $builder->add('sport', ChoiceType::class, array(
            'choices' => array(
                'badminton' => 'createTournamentType.sport.badminton',
                'tennis' => 'createTournamentType.sport.tennis',
                'other' => 'createTournamentType.sport.other'
            ),
            'label' => 'createTournamentType.sport.label',
            'mapped' => false,
            'attr' => array("formComment"=>"createTournamentType.sport.formComment"),
        ));
        $builder->add('nrSets', IntegerType::class, array(
            'label' => 'tournamentType.nrSets.label',
            'required' => false,
        ));

        // financial
        $builder->add('financialMethod', ChoiceType::class, array(
            'choices' => array(
                'free' => 'financialType.financialMethod.free',
                'invoice' => 'financialType.financialMethod.invoice',
                'payments' => 'financialType.financialMethod.payments'
            ),
            'label' => 'financialType.financialMethod.label',
            'attr' => array("formComment"=>"financialType.financialMethod.formComment"),
        ));

        // finish
        $builder->add('conditionsAgree', CheckboxType::class, array(
            'required' => true,
            'mapped' => false,
            'constraints' => new Assert\True(array("message"=>"createTournamentType.conditionsAgree.constraint")),
        ));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\Tournament',
            'translation_domain' => 'settings',
        ));

        $resolver->setRequired(array(
            'person',
        ));

        $resolver->setAllowedTypes(array(
            'person' => 'TS\ApiBundle\Entity\Person',
        ));
    }
}