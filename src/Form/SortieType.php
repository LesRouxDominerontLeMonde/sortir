<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('debut')
            ->add('duree')
            ->add('fin_inscription')
            ->add('inscriptions_max')
            ->add('created_at')
            ->add('updated_at')
            ->add('archivee')
            ->add('etat')
            ->add('lieu')
            ->add('organisateur')
            ->add('participants')
            ->add('campus_origine')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
