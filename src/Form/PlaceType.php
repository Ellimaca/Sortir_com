<?php

namespace App\Form;

use App\Entity\Place;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Lieu'
            ])
            ->add('street', TextType::class, [
                'label' => 'Rue'
            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude'
            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
