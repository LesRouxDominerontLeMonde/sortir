<?php

namespace App\Controller;

use App\Entity\Photo;
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

class UpdateProfileController extends AbstractController
{
    /**
     * @Route("/update/profil", name="app_update_profil")
     */
    public function update(Request                     $request,
                           UserPasswordHasherInterface $userPasswordHasher,
                           UserAuthenticatorInterface  $userAuthenticator,
                           AppAuthenticator            $appAuthenticator,
                           EntityManagerInterface      $entityManager): Response
    {
        if(!$this->getUser()){
            throw new AccessDeniedHttpException('Accès refusé.');
        }

        $user = $this->getUser();
        $oldPassword = $user->getPassword();
        $form = $this->createForm(UpdateProfileFormType::class, $user);

        $form->handleRequest();

        if($form->isSubmitted() && $form->isValid()) {
            $user->setActif(true);
            if($form->get('password')->getData() != '') {
                $user->setPassword($userPasswordHasher->hashPassword($form->get('password')->getData()));
            } else {
                $user->setPassword($oldPassword);
            }
            $photo = $form->get('photo')->getData();
            // Si il y a un fichier dans les données du formulaire et qu'il n'existe pas
            $fs = new Filesystem();
            if ($photo && !$fs->exists($this->getParameter('profile_image_directory', $photo->getClientOriginalName())));
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
                $appAuthenticator,
                $request
            );
        }

        return $this->render('update_profile/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
