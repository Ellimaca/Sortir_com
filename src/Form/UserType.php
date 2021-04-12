<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo')

            ->add('firstName', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('lastName', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Nom'
            ])
            ->add('phoneNumber', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Téléphone'
            ])
            ->add('email', EmailType::class)

            ->add('newPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Le nouveau mot de passe et sa confirmation doivent correspondre!',
                'first_options'  => array('label' => 'Nouveau mot de passe'),
                'second_options' => array('label' => 'Confirmation'),
                'mapped' => false
            ))

            ->add('campus', EntityType::class, [
                'label' => "Campus",
                'class' => Campus::class,
                'choice_label' => 'name',
                'choice_value'  => ChoiceList::value($this, 'name')
            ])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer'])




        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}
