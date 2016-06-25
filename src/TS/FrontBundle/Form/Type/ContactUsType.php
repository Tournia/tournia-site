<?php
namespace TS\FrontBundle\Form\Type;

use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ContactUsType extends AbstractType
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
                'mapped' => false
            ),
            'constraints' => array(
                new NotBlank(array('message' => 'contactUsType.name.notblank')),
            ),
        ));
        $builder->add('email', EmailType::class, array(
            'label' => false, 
            'attr' => array(
                'mapped' => false
            ),
            'constraints' => array(
                new NotBlank(array('message' => 'contactUsType.email.notblank')),
                new Email(array('checkMX' => true, 'checkHost' => true)),
            ),
        ));
        $builder->add('phone', TextType::class, array(
            'label' => false, 
            'required' => false,
            'attr' => array(
                'mapped' => false
            )
        ));
        $builder->add('message', TextareaType::class, array(
            'label' => false, 
            'attr' => array(
                'mapped' => false
            ),
            'constraints' => array(
                new NotBlank(array('message' => 'contactUsType.message.notblank')),
            ),
        ));
        $builder->add('captcha', CaptchaType::class, array(
            'label' => false,
        ));
    }
}