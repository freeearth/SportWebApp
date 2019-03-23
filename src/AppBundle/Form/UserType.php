<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('username', TextType::Class, array("label"=>"User name"))
                ->add('email', EmailType::Class)
                ->add('plainPassword', RepeatedType::Class, [
                        "type" => PasswordType::Class,
                        "first_options" => ["label" => "Password"],
                        "second_options" => ["label" => "Repeat password"]
                        ])
                ->add('roles',ChoiceType::class, array( 'multiple' => false, 
                            'expanded' => false, // render selects
                            'label' => 'User role',
                            'choices' => [
                                'publusher' => 'ROLE_PUBLISHER',
                                'viewer' => 'ROLE_USER',
                            ]));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }


}
