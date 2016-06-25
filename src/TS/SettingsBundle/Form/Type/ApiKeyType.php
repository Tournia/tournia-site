<?php
namespace TS\SettingsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApiKeyType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'apiKeyType.name.label',
            'required' => true,
            'constraints' => new NotBlank(),
        ));
        $builder->add('writeAccess', ChoiceType::class, array(
            'label' => 'apiKeyType.writeAccess.label',
            'required' => true,
            'choices' => array(
                'apiKeyType.writeAccess.read' => false,
                'apiKeyType.writeAccess.readWrite' => true,
            ),
            'choices_as_values' => true,
            'expanded' => true,
            'multiple' => false,
        ));
        $builder->add('secret', TextType::class, array(
            'label' => 'apiKeyType.secret.label',
            'required' => false,
            'disabled' => true,
            'empty_data' => 'apiKeyType.secret.empty',
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TS\ApiBundle\Entity\ApiKey',
            'translation_domain' => 'settings',
        ));
    }
}