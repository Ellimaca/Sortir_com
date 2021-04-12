<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\User;
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
           // ->add('dateTimeEnd', DateTimeType::class, ['widget' => 'single_text',
            //    'label' => 'Date et heure de fin'
            //])
            ->add('registrationDeadline',DateTimeType::class, [  'widget' => 'single_text',
                'label' => 'Date limite inscription'
            ])
            ->add('maxNumberParticipants', IntegerType::class, [
                'label' => 'Nombre de places'
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description et infos'
            ])
            // TODO mettre le campus de l'utilisateur figé
            ->add('campus', EntityType::class, [
                'label' => "Campus",
                'class' => Campus::class,
                'choice_label' => 'name',
                'choice_value' => ChoiceList::value($this, 'name'),
            ])
            ->add('city', EntityType::class, [
                'label' => 'Ville',
                'class' => City::class,
                'choice_label' => 'name',  'mapped' => false
            ])

            ->add('place', EntityType::class, [
                'label' => 'Lieu',
                'class' => Place::class,
                'choice_label' => 'name',
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('submit', SubmitType::class, ['label'=> 'publier la sortie'])

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
