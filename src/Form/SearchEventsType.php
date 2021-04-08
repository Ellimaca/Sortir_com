<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\ChoiceList\ChoiceList;

class SearchEventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'choice_value'  => ChoiceList::value($this, 'name')
            ])
            ->add('searchBar', TextType::class, [
                'required' => false
            ])
            ->add('dateStart')
            ->add('dateEnd')
            ->add('isOrganisedByMe', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur/trice",
                'required' => false
            ])
            ->add('isAttendedByMe', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit/e",
                'required' => false
            ])
            ->add('isNotAttendedByMe', CheckboxType::class, [
                'label' => "Sorties auxquelles je ne suis pas inscrit/e",
                'required' => false
            ])
            ->add('isFinished', CheckboxType::class, [
                'label' => "Sorties passées",
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
