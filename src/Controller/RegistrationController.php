<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Form\PasswordUpdateFormType;
use App\Form\RegistrationFormType;
use App\Form\UpdateProfileFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

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
            $user->setActif(true)
                ->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            // Ajout de la photo

           $this->addPhoto($form->get('photo')->getData(), $user, $entityManager);

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
            'title' => 'Enregistrement',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profil/update", name="app_update_profil")
     */
    public function update(Request                     $request,
                           UserAuthenticatorInterface  $userAuthenticator,
                           AppAuthenticator            $appAuthenticator,
                           EntityManagerInterface      $entityManager): Response
    {
        if(!$this->getUser()){
            throw new AccessDeniedHttpException('Accès refusé.');
        }

        $user = $this->getUser();
        $form = $this->createForm(UpdateProfileFormType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user->setActif(true);

            $this->addPhoto($form->get('photo')->getData(), $user, $entityManager);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre formulaire a été soumis avec succès !');

            return $userAuthenticator->authenticateUser(
                $user,
                $appAuthenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
            'title' => 'Mise à jour du profil',
        ]);
    }

    /**
     * @Route("/profil/newpassword", name="app_password_profil")
     */
    public function password(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface $userAuthenticator,
                             AppAuthenticator $authenticator,
                             EntityManagerInterface $entityManager): Response
    {
        if(!$this->getUser()){
            throw new AccessDeniedHttpException('Accès refusé.');
        }

        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateFormType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès !');

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }
        return $this->render('registration/password.html.twig',
        [
            'form' => $form->createView(),
        ]);

    }
    private function addPhoto($photo, $user, $em)
    {
        $fs = new Filesystem();
        if ($photo && !$fs->exists($this->getParameter('profile_image_directory', $photo->getClientOriginalName())))
        {
            // On vérifie le type
            $mime = $photo->getMimeType();
            if($mime === 'image/jpeg' || $mime === 'image/png'){
                // Si le type es ok, on donne un nom de fichier unique
                $filename = uniqid().'.'.$photo->guessExtension();
                // On l'enregistre dans le dossier enregistré dans config/services.yaml
                $photo->move($this->getParameter('profile_image_directory'), $filename);
                // On crée l'entité pour enregistrer le fichier dans la base de données
                $em->persist($user);
                $photoEntity = new Photo();
                $photoEntity->setNomFichier($filename)
                    ->setUtilisateur($user)
                    ->setActive(true);
                $em->persist($photoEntity);
                $em->flush();
                // Puis on lie la photo au profil utilisateur
                $user->addPhoto($photoEntity);
            }
        }
    }

}
