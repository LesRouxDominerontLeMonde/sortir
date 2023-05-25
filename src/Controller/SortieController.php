<?php

namespace App\Controller;


use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\FilterSortiesFormType;
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
     * @Route("/sorties/{id}", name="app_sortie", requirements={"id"="\d+"})
     */
    public function index(Request $request, SortieRepository $sortieRepository): Response
    {
        $id = $request->get('id');
        $sortie = $sortieRepository->findOneBy(['id' => $id]);

        if (!$sortie) {
            throw $this->createNotFoundException('La sortie demandée n\'existe pas.');
        }

        return $this->render('sortie/sortie.html.twig', [
            'sortie' => $sortie,
        ]);
    }
    /**
     * @Route("/sorties", name="app_sorties")
     */
    public function listeSorties(Request $request, SortieRepository $sortieRepository): Response
    {
        $sortie = new Sortie();
        $sorties = $sortieRepository->findAll();
        $filterForm = $this->createForm(FilterSortiesFormType::class, $sortie, []);
        $filterForm->handleRequest($request);
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            // Les données du formulaire sont valides, vous pouvez récupérer les critères de filtrage
            $filtreCampus = $filterForm->get('campus_origine')->getData();
            $filtreNom = $filterForm->get('nom')->getData();
            $filtreDebut = $filterForm->get('debut')->getData();
            $filtreArchivee = $filterForm->get('archivee')->getData();
            $filtreEtat = $filterForm->get('etat')->getData();
            $filtreOrganisateur = $filterForm->get('organisateur')->getData();
            $filtreInscrit = $filterForm->get('inscrit')->getData();

            // Utilisez les critères de filtrage pour ajuster votre requête et récupérer les sorties correspondantes
            $sorties = $this->getSortiesFiltrees($filtreCampus, $filtreNom, $filtreDebut, $filtreArchivee, $filtreEtat, $filtreOrganisateur, $filtreInscrit);
        }
        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sorties,
            'filter_form' => $filterForm->createView()
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
    public function inscriptionSortie (Request $request, ManagerRegistry $doctrine, SortieRepository $sortieRepository): Response
    {
        $em = $doctrine -> getManager();
        $id = $request->get('id');
        $sortie = $sortieRepository->findOneBy(['id' => $id]);

        if ($sortie -> getEtat() -> getLibelle() !== 'Ouverte') {
            $this->addFlash('danger', "Impossible d'accéder a cette sortie");
            return $this->redirectToRoute('app_sorties', ['id' => $sortie->getId()]);
        }

        if ($sortie -> limiteInscription())
        {
            $this -> addFlash('danger', 'Trop tard :( Sortie complète');
            return $this -> redirectToRoute('app_sorties', ['id' => $sortie -> getId()]);
        }
        $user = $this->getUser();
        $inscription = $sortie->addParticipant($user);

        $em -> persist($inscription);
        $em -> flush();

        #actualiser le nombre de participant apres inscription
        $em -> refresh($sortie);

        #si la sortie est complete l'etat passe en clôturée
        if ($sortie ->limiteInscription())
        {
            $etat = $sortie->getEtat();
            $etat->setLibelle('Clôturée');
        }
        $this -> addFlash('success', 'Vous êtes inscrit. Félicitation :)');
        return $this -> redirectToRoute('app_sorties', ['id' => $sortie ->getId()]);
    }

    /**
     * @Route("/sortie/désinscription/{id}", name="app_sortie_désinscription", requirements={"id"="\d+"})
     */
    public function desinscriptionSortie(Request $request, EntityManagerInterface $em, SortieRepository $sortieRepository, ManagerRegistry $doctrine)
    {
        $em = $doctrine -> getManager();
        $id = $request->get('id');
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        $user = $this->getUser();
        $inscriptionMatch = $sortie->getParticipants()->contains($user);
        if($inscriptionMatch)
        {
            $desinscription = $sortie->removeParticipant($user);
            $em -> persist($desinscription);
            $em -> flush();

            $em -> refresh($sortie); /* Pour liberer la place annulé et la rendre disponible */

            $this -> addFlash('success', 'Vous êtes maintenant désinscrit. Au plaisir  :)');
            return $this -> redirectToRoute('app_sorties', ['id' => $sortie ->getId()]);
        } else {
            $this->addFlash('error', 'Inscription non trouvée.');
            return $this->redirectToRoute('app_sorties');
        }
    }

    private function getSortiesFiltrees($filtreCampus, $filtreNom, $filtreDebut, $filtreArchivee, $filtreEtat, $filtreOrganisateur, $filtreInscrit)
    {
        $user = $this->getUser();
        $entityManager = $this->managerRegistry->getManager();
        $repository = $entityManager->getRepository(Sortie::class);

        $queryBuilder = $repository->createQueryBuilder('s');

        // Appliquer les critères de filtrage

        if ($filtreCampus!== null) {
            $queryBuilder->andWhere('s.campus_origine = :campus')
                ->setParameter('campus', $filtreCampus);
        }

        if ($filtreNom!== null) {
            $queryBuilder->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%'.$filtreNom.'%');
        }

        if ($filtreDebut !== null && isset($filtreDebut['start'], $filtreDebut['end'])) {
            $start = $filtreDebut['start']->format('Y-m-d H:i:s');
            $end = $filtreDebut['end']->format('Y-m-d H:i:s');

            $queryBuilder->andWhere('s.debut >= :start')
                ->andWhere('s.debut <= :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        if ($filtreArchivee !== null) {
            $queryBuilder->andWhere('s.archivee = :archivee')
                ->setParameter('archivee', (bool) $filtreArchivee);
        }

        if ($filtreEtat !== null) {
            $queryBuilder->andWhere('s.etat = :etatId')
                ->setParameter('etatId', $filtreEtat->getId());
        }

        if ($filtreOrganisateur) {

            $queryBuilder->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $user);
        }

        if ($filtreInscrit) {
            $subQuery = $entityManager->createQueryBuilder()
                ->select('s1.id')
                ->from('App\Entity\Sortie', 's1')
                ->join('s1.participants', 'p')
                ->where('p = :user')
                ->andWhere('s1 = s');

            $queryBuilder->andWhere($queryBuilder->expr()->exists($subQuery->getDQL()))
                ->setParameter('user', $user);
        }



        $query = $queryBuilder->getQuery();
        $sorties = $query->getResult();

        return $sorties;
    }
}
