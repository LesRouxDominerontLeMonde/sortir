<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFormType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la sortie'])
            ->add('debut', DateTimeType::class, ['label' => 'Date et heure de la sortie'])
            ->add('duree', DateIntervalType::class, ['label' => 'Durée',
                'widget' => 'integer',
                'with_years' => false,
                'with_months' => false,
                'with_days' => false,
                'with_hours' => false,
                'with_minutes' => true,])
            ->add('description', TextType::class, ['label' => 'Description'])
            ->add('campus_origine', EntityType::class, ['class' => 'App\Entity\Campus',
                'choice_label' => 'nom'])
            ->add('fin_inscription', DateType::class, ['label' => 'Date limite d\'inscription'])
            ->add('inscriptions_max', IntegerType::class, ['label' => 'Nombre de places'])
            ->add('ville', EntityType::class, ['class' => 'App\Entity\Ville',
                'choice_label' => 'nom', 'mapped' => false, 'placeholder' => 'Sélectionnez une ville',]);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $villeId = $data['ville'];
                $ville = $this->entityManager->getRepository(Ville::class)->find($villeId);
                if (!empty($ville)) {
                    // Récupérer les lieux en fonction de la ville sélectionnée
                    $lieux = $this->entityManager->getRepository(Lieu::class)->findByVille($ville);

                    $form->add('lieu', EntityType::class, [
                        'class' => Lieu::class,
                        'choices' => $lieux,
                        'choice_label' => 'nom',
                        'placeholder' => 'Sélectionnez un lieu',
                        'required' => false,
                    ]);
                }
            });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'lieu_repository' => null
        ]);
    }
}
