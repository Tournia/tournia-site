<?php
namespace TS\AccountBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom field
        $builder->add('registrationName', TextType::class, array(
            'constraints' => array(
                new NotBlank(array('message' => 'registrationFormType.registrationName.notBlank')),
            ),
        ));
    }

    public function getName()
    {
        return 'ts_user_registration';
    }
}