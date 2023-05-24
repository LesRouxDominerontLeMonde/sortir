<?php

namespace App\Controller;


use App\Entity\Etat;
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
    public function createSortie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $etatEnregistre = $this->managerRegistry->getRepository(Etat::class)->find(1);
        $etatPublie = $this->managerRegistry->getRepository(Etat::class)->find(2);
        $user = $this->getUser();
        $form = $this->createForm(SortieFormType::class, $sortie, []);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $sortie->setCreatedAt(new \DateTimeImmutable());
            $sortie->setArchivee(false);
            $sortie->setOrganisateur($user);
            $organisateur = $sortie->getOrganisateur();
            $campusOrganisateur = $organisateur->getCampus();
            $sortie->setCampusOrigine($campusOrganisateur);
            $clickedButton = $form->getClickedButton();
            if ($clickedButton && $clickedButton->getName() === 'enregistrer') {
                $sortie->setEtat($etatEnregistre);
            } elseif ($clickedButton && $clickedButton->getName() === 'publier') {
                $sortie->setEtat($etatPublie);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            if ($clickedButton && $clickedButton->getName() === 'enregistrer') {
                $this->addFlash('success', 'Votre sortie a été enregistrée avec succès !');
            } elseif ($clickedButton && $clickedButton->getName() === 'publier') {
                $this->addFlash('success', 'Votre formulaire a été publiée avec succès !');
            }
            return $this->redirectToRoute('app_home');
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


    /**
     * @Route ("/sortie/inscription/{id}", name="app_sortie_inscription", requirements={"id"="\d+"})
     */
    public function inscriptionSortie (ManagerRegistry $doctrine, Sortie $sortie, Etat $etat): Response
    {
        $em = $doctrine -> getManager();
        $inscriptionRepo = $doctrine -> getRepository(Sortie::class);

        if ($sortie -> getEtat() -> getLibelle() !== 'Ouverte') {
            $this->addFlash('danger', "Impossible d'accéder a cette sortie");
            return $this->redirectToRoute('app_sorties', ['id' => $sortie->getId()]);
        }

                $inscription = new Sortie();
                $inscription -> setUser ($this -> getUser());
                $inscription -> setSortie ($sortie);

                $em -> persist($inscription);
                $em -> flush();

                $this -> addFlash('success', 'Vous êtes inscrit. Félicitation :)');
                return $this -> redirectToRoute('app_sorties', ['id' => $sortie ->getId()]);


    }
}
