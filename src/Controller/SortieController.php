<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SortieController
 * @package App\Controller
 * @Route("/sortie")
 */
class SortieController extends AbstractController
{
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="app_sortie_index")
     */
    public function index(SortieRepository $sortieRepository, CampusRepository $campusRepository, EtatRepository $etatRepository, PaginatorInterface $paginator, Request $request, ParticipantRepository $participantRepository): Response
    {
        $campus = $campusRepository->findAll();
        $user = $this->getUser();
        $utilisateur = $participantRepository->findOneBy(['username' => $user->getUsername()]);

        $filtreCampus = $request->query->get('campus');
        $filtreMot = $request->query->get('nom_sortie_contient');
        $debutPeriode = strtotime($request->query->get('date_debut'));
        $finPeriode = strtotime($request->query->get('date_fin'));
        if ($debutPeriode and $finPeriode) {
            $dateDebut = date('Y-m-d 00:00:00', $debutPeriode);
            $dateFin = date('Y-m-d 00:00:00', $finPeriode);
            var_dump($dateDebut);
            var_dump($dateFin);
        } else {
            $dateDebut = null;
            $dateFin = null;
        }
        $checkOrganisateur = $request->query->get('sortie_organisateur');
        if ($checkOrganisateur) {
            $filtreOrganisateur = $utilisateur;
        } else {
            $filtreOrganisateur = null;
        }
        $checkSortiePassee = $request->query->get('sorties_passees');
        if ($checkSortiePassee) {
            $filtreSortiePassee = $etatRepository->findOneBy(['libelle' => 'Passée']);
        } else {
            $filtreSortiePassee = null;
        }


        $sorties = $paginator->paginate(
            $sortieRepository->findListOfSortiesWithFilters($filtreCampus, $filtreMot, $dateDebut, $dateFin, $filtreOrganisateur, $filtreSortiePassee),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('sortie/index.html.twig', [
            'campus' => $campus,
            'sorties' => $sorties,
        ]);
    }

    /**
     * @Route ("/creer", name="app_sortie_creer", methods={"GET", "POST"})
     */
    public function creer(Request $request, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie;
        $participant = $this->getUser();

        $sortie->setDateHeureDebut(new \DateTime('now'));
        $sortie->setDateLimiteInscription(new \DateTime('now'));
        $sortie->setSiteOrganisateur($participant->getCampus());

        $etat = $etatRepository->findOneBy(array('libelle' => 'Créée'));

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setOrganisateur($participant);
            $sortie->setEtat($etat);
            $this->em->flush();

            return $this->redirectToRoute('app_sortie_index');
        }
        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/modifier/{id<[0-9]+>}", name="app_sortie_modifier")
     */
    public function modifier(Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, $id): Response
    {
        $sortie = $sortieRepository->find($id);
        $participant = $this->getUser();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setOrganisateur($participant);
            $sortie->setSiteOrganisateur($participant->getCampus());
            $this->em->persist($sortie);
            $this->em->flush();
            return $this->redirectToRoute('app_sortie_index');
        }
        return $this->render('sortie/modifier.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id<[0-9]+>}", name="app_sortie_afficher", methods={"GET"})
     */
    public function afficher($id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $participants = $sortieRepository->findParticipants($id);

        return $this->render('sortie/afficher.html.twig', [
            'sortie' => $sortie,
            'participants' => $participants,
        ]);
    }

}
