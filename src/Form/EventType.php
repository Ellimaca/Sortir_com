<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder

            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie'
            ])
            ->add('dateTimeStart', DateTimeType::class, ['label' => 'Date et heure de la sortie',
                'widget' => 'single_text'
            ])
            ->add('registrationDeadline',DateTimeType::class, [  'widget' => 'single_text',
                'label' => 'Date limite inscription'
            ])
            ->add('maxNumberParticipants', IntegerType::class, [
                'label' => 'Nombre de places'
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée en minutes'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description et infos'
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('submit', SubmitType::class, ['label'=> 'publier la sortie'])
            ->add('campus', EntityType::class, [
                'label' => "Campus",
                'class' => Campus::class,
                'choice_label' => 'name',
                'choice_value' => ChoiceList::value($this, 'name'),
                'disabled' => true
            ])
            ->add('city', EntityType::class, [
                'label' => 'Ville',
                'class' => City::class,
                'choice_label' => 'name',
                'mapped' => false,
                'placeholder' => 'Définir une ville'
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'label' => 'Lieu',
                'placeholder' => 'Définir un lieu',
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'attr' => [
                'novalidate' => 'novalidate'
                ]
        ]);
    }
}
