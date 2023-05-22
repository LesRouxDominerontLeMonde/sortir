<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\SortieFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @Route("/sortie", name="app_sortie")
     */
    public function index(): Response
    {
        return $this->render('sortie/sortie.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }
    /**
     * @Route("/sorties", name="app_sorties")
     */
    public function listeSorties(SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
{
    $sorties = $sortieRepository->findAll();
    return $this->render('sortie/liste.html.twig', [
        'sorties' => $sorties
    ]);
}
    /**
     * @Route("/sortie/create", name="app_sortie_create")
     */
    public function createSortie(Request $request, EntityManagerInterface $entityManager,
                                 ManagerRegistry $doctrine): Response
    {
        $sortie = new Sortie();
        $etat = $this->managerRegistry->getRepository(Etat::class)->find(1);
        $user = $this->getUser();
        $form = $this->createForm(SortieFormType::class, $sortie, [
            'lieu_repository' => $doctrine->getRepository(Lieu::class),
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $sortie->setCreatedAt(new \DateTimeImmutable());
            $sortie->setArchivee(false);
            $sortie->setEtat($etat);
            $sortie->setOrganisateur($user);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->render('sortie/create.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/sortie/edit/{id}", name="app_sortie_edit", requirements={"id"="\d+"})
     */
    public function editSortie(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        // TODO : Code pour récupérer l'item à modifier et renvoyer une 404 si paq ok
        return $this->render('sortie/edit.html.twig');
    }

    /**
     * @Route("/sortie/delete/{id}", name="app_sortie_delete", requirements={"id"="\d+"})
     */
    public function deleteSortie(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        // TODO : Code pour récupérer l'item à supprimer et renvoyer une 404 si paq ok
        return $this->render('sortie/delete.html.twig');
    }
}
