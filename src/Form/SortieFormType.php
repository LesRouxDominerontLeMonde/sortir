<?php

namespace App\Form;


use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $uniqueCities = $options['unique_cities'];
        dump($uniqueCities);

        $builder
            ->add('nom', TextType::class, ['label'=>'Nom de la sortie'])
            ->add('debut', DateTimeType::class, ['label'=>'Date et heure de la sortie'])
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

            ->add('fin_inscription', DateType::class, ['label'=>'Date limite d\'inscription'])
            ->add('inscriptions_max', IntegerType::class, ['label'=>'Nombre de places'])

            ->add('lieu', ChoiceType::class, [
                'choices' => $uniqueCities,
                'label'=>'Ville',]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'unique_cities' => [],
        ]);
    }
}
