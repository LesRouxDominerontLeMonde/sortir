<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ProfilController extends AbstractController
{
    protected $projectDir;

    public function __construct(KernelInterface $kernel, UserRepository $userRepository)
    {
        $this->projectDir = $kernel->getProjectDir();
    }
    /**
     * @Route("/profil", name="app_profil")
     */
    public function index(Security $security): Response
    {
        $user = $security->getUser();
        $photoName = $user->getCurrentPhoto();

        return $this->render('profil/profil.html.twig', [
            'user' => $user,
            'photo' => $user->getCurrentPhotoName(),
            'photo_path' => $this->getParameter('profile_image_directory') .'/',
        ]);
    }

    /**
     * @Route("profil/{id}", name="app_profil_affiche", requirements={"id"="\d+"})
     */
    public function afficheProfilById(Request $request, ManagerRegistry $doctrine, UserRepository $userRepository)
    {
        $id = $request->get('id');
        $user = $userRepository->findOneBy(['id' => $id]);
        if (!$user) {
            throw $this->createNotFoundException('Le participant demandé n\'existe pas.');
        }

        return $this->render('profil/afficheParticipant.html.twig', [
            'participant' => $user,
            'photo' => $user->getCurrentPhotoName(),
            'photo_path' => $this->getParameter('profile_image_directory') .'/',
        ]);
    }

    /**
     * @Route("/profil/edit", name="app_profil_edit")
     */
    public function edit(): Response
    {
        return $this->render('profil/profil.html.twig');
    }
}
