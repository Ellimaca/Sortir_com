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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
                'label' => 'Durée'
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
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'label' => 'Lieu',
                'placeholder' => 'Définir un lieu',
                'mapped' => false
            ])
        ;


/*        $formModifier = function (FormInterface $form, City $city = null) {
            $places = null === $city ? [] : $city->getPlaces();
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();

            $formModifier($event->getForm(), $data->getPlace());

             }
        );
        $builder->get('city')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {

                $city = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $city);
            }
        );*/
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
