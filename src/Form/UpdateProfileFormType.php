<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data'];

        $selectedCampusId = $user->getCampusId();
        $builder
        ->add('pseudo', TextType::class, [
            'data' => $user->getPseudo()
        ])
        ->add('firstname', TextType::class, [
            'data' => $user->getFirstname(),
        ])
        ->add('name', TextType::class, [
            'data' => $user->getName(),
        ])
        ->add('phoneNumber', TextType::class, [
            'data' => $user->getPhoneNumber(),
        ])
        ->add('email', EmailType::class)
        ->add('campus', EntityType::class, [
            'class' => Campus::class,
            'choice_label' => 'nom',
            'placeholder' => 'Choisissez votre campus',
            'choice_value' => 'id',
            'choice_attr' => function ($choice, $key, $value) use ($selectedCampusId) {
                // Définir l'attribut 'selected' pour la valeur par défaut
                if ($selectedCampusId && $selectedCampusId === $value) {
                    return ['selected' => 'selected'];
                }

                return [];
            },
        ])
        ->add('photo', FileType::class, [
            'required' => false,
            'mapped' => false,
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
