<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\CampusRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface $userAuthenticator,
                             AppAuthenticator $authenticator,
                             EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setActif(true);
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            // Ajout de la photo

            $photo = $form->get('photo')->getData();
            // Si il y a un fichier dans les données du ormulaire
            if ($photo)
            {
                // On vérifie le type
                $mime = $photo->getMimeType();
                if($mime === 'image/jpeg' || $mime === 'image/png'){
                    // Si le type es ok, on donne un nom de fichier unique
                    $filename = uniqid().'.'.$photo->guessExtension();
                    // On l'enregistre dans le dossier enregistré dans config/services.yaml
                    $photo->move($this->getParameter('profile_image_directory'), $filename);
                    // On crée l'entité pour enregistrer le fichier dans la base de données
                    $entityManager->persist($user);
                    $photoEntity = new Photo();
                    $photoEntity->setNomFichier($filename)
                        ->setUtilisateur($user)
                        ->setActive(true);
                    $entityManager->persist($photoEntity);
                    $entityManager->flush();
                    // Puis on lie la photo au profil utilisateur
                    $user->addPhoto($photoEntity);
                }
            }


            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            $this->addFlash('success', 'Votre formulaire a été soumis avec succès !');

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


}
