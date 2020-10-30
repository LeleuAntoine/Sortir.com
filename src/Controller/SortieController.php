<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
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
    private $flashy;
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(FlashyNotifier $flashy, EntityManagerInterface $em)
    {
        $this->flashy = $flashy;
        $this->em = $em;
    }

    /**
     * @Route("/", name="app_sortie_index")
     */
    public function index(SortieRepository $sortieRepository, CampusRepository $campusRepository,
                          EtatRepository $etatRepository, PaginatorInterface $paginator,
                          Request $request, ParticipantRepository $participantRepository): Response
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
            'utilisateur' => $utilisateur,
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
            $this->em->persist($sortie);
            $this->em->flush();

            $this->flashy->success('Sortie créer !');
            return $this->redirectToRoute('app_sortie_index');
        }
        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/modifier/{id<[0-9]+>}", name="app_sortie_modifier")
     */
    public function modifier(Request $request, SortieRepository $sortieRepository, $id): Response
    {
        $sortie = $sortieRepository->find($id);
        $participant = $this->getUser();
//            Vérifie si la personne à les droit pour la modification
        if (in_array("ROLE_ADMIN", $participant->getRoles())
            and $sortie->getEtat()->getlibelle() === "Créée" or
            $participant->getUsername() === $sortie->getOrganisateur()->getUsername()
            and $sortie->getEtat()->getlibelle() === "Créée") {

            $form = $this->createForm(SortieType::class, $sortie);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em->flush();
                $this->flashy->success('Sortie modifié avec succé !');
            }
        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaire !', '#');
            return $this->redirectToRoute('app_sortie_index');
        }
        return $this->render('sortie/modifier.html.twig', ['form' => $form->createView()]);
    }


    /**
     * @Route("/{id<[0-9]+>}", name="app_sortie_afficher", methods={"GET"})
     */
    public
    function afficher($id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        if ($sortie->getEtat()->getLibelle() !== "Créée") {
            return $this->render('sortie/afficher.html.twig', [
                'sortie' => $sortie,
            ]);
        } else {
            $this->flashy->error('Visualisation impossible');
            return $this->redirectToRoute('app_sortie_index');
        }
    }

    /**
     * @Route("/{id}/inscription", name="app_sortie_s_inscrire", requirements={"id": "\d+"})
     */
    public
    function sInscrire(Sortie $sortie, ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->findOneBy(['username' => $this->getUser()->getUsername()]);

        $sortie->ajouterParticipant($participant);

        $this->em->persist($sortie);
        $this->em->flush();

        $this->flashy->success('Vous êtes bien inscrit à la sortie ' . $sortie->getNom());
        return $this->redirectToRoute('app_sortie_index');

    }

    /**
     * @Route("/{id}/desinscription", name="app_sortie_se_desinscrire", requirements={"id": "\d+"})
     */
    public
    function seDesinscrire(Sortie $sortie, ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->findOneBy(['username' => $this->getUser()->getUsername()]);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée');
        }

        $sortie->enleverParticipant($participant);

        $this->em->persist($sortie);
        $this->em->flush();

        $this->flashy->success('Vous êtes bien désinscrit à la sortie ' . $sortie->getNom());
        return $this->redirectToRoute('app_sortie_index');

    }

//    /**
//     * @Route("/lieu/ajouter", name="app_lieu_ajouter")
//     */
//    public function ajoutLieu(Ville $ville, Request $request){
//        $lieu = new Lieu();
//        $form = $this->createForm(Lieu::class, $lieu);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()){
//            $lieu->setVille($ville);
//
//            $this->em->persist($lieu);
//            $this->em->flush();
//
//            return $this->redirectToRoute('app_sortie_creer');
//        }
//        return $this->render(
//            'sortie/lieu.html.twig', array(
//                'form' => $form->createView(),
//                'ville' => $ville
//            )
//        );
//    }
}
