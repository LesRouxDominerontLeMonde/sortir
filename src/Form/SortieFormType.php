<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label'=>'Nom de la sortie'])
            ->add('debut', DateTimeType::class, ['label'=>'Date et heure de la sortie'])
            ->add('fin_inscription', DateType::class, ['label'=>'Date limite d\'inscription'])
            ->add('inscriptions_max', IntegerType::class, ['label'=>'Nombre de places'])
            ->add('duree', DateIntervalType::class, ['label'=>'Durée',
                'widget'      => 'integer',
                'with_years'  => false,
                'with_months' => false,
                'with_days'   => false,
                'with_hours'  => false,
                'with_minutes'=> true,])
            ->add('description', TextType::class, ['label'=>'Description'])
            ->add('campus_origine', EntityType::class, ['class' => 'App\Entity\Campus',
                'choice_label' => 'nom'])
            ->add('lieu', EntityType::class, ['class'=>Lieu::class,'choice_label'=>'nom'])
            ->add('organisateur', EntityType::class,['class'=>User::class, 'choice_label'=>'pseudo']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
