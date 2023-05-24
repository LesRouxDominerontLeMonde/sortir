<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterSortiesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus_origine', EntityType::class, ['class'=>'App\Entity\Campus',
                'choice_label' => 'nom', 'required' => false])
            ->add('nom', SearchType::class, ['label'=>'Le nom de la sortie contient :', 'required' => false])
            ->add('debut', DateRangePickerType::class, [
                'label' => 'Date de début', 'required' => false, 'mapped'=>false
            ])
            ->add('archivee', CheckboxType::class, ['label'=>'Sorties passées', 'required' => false])
            ->add('etat', EntityType::class, ['class'=>'App\Entity\Etat',
                'choice_label' => 'libelle', 'required' => false])
            ->add('organisateur', CheckboxType::class, ['label'=>"Sorties dont je suis l'organisateur/trice",
                'required' => false, 'mapped'=>false])
            ->add('inscrit', CheckboxType::class, ['label'=>'Sorties auxquelles je suis inscrit/e',
                'mapped'=>false, 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
