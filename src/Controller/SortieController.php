<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
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
     * @Route("/sortie/create")
     */
    public function createSortie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieFormType::class, $sortie);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $sortie->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->render('sortie/create.html.twig', [
            'form'=>$form->createView()
        ]);

    }
}
