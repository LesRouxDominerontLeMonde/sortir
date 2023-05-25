<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data'];
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
            'data' => 'O'.$user->getPhoneNumber(),
        ])
        ->add('email', EmailType::class)
        ->add('campus', EntityType::class, [
            'class' => Campus::class,
            'choice_label' => 'campus',
            'placeholder' => 'Choisissez votre campus',
            'choice_value' => 'id',
            'choice_attr' => function ($choice, $key, $value) use ($user) {
                // Définir l'attribut 'selected' pour la valeur par défaut
                if ($user->getCampus() && $user->getCampus()->getId() === $value) {
                    return ['selected' => 'selected'];
                }

                return [];
            },
        ])
        ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passes doivent être identiques.',
            'options' => ['attr' => ['autocomplete' => 'new-password']],
            'required' => false,
            'constraints' => [
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre mot de passe doit contenir au minimum {{ limit }} caractères',
                    'max' => 4096,
                ]),
            ],
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
