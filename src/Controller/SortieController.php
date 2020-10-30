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
        $utilisateur = $participantRepository->findOneBy(['username' => $this->getUser()->getUsername()]);

        //Sortie sur le campus
        $filtreCampus = $request->query->get('campus');

        //Sortie dont le nom commence par
        $filtreMot = $request->query->get('nom_sortie_contient');

        //Sortie entre debutPeriode et finPeriode
        $debutPeriode = strtotime($request->query->get('date_debut'));
        $finPeriode = strtotime($request->query->get('date_fin'));
        if ($debutPeriode and $finPeriode) {
            $dateDebut = date('Y-m-d H:i:s', $debutPeriode);
            $dateFin = date('Y-m-d H:i:s', $finPeriode);
        } else {
            $dateDebut = null;
            $dateFin = null;
        }
        //Sorties dont je suis l'organisateur
        $checkOrganisateur = $request->query->get('sortie_organisateur');
        if ($checkOrganisateur) {
            $filtreOrganisateur = $utilisateur;
        } else {
            $filtreOrganisateur = null;
        }

        //Sorties auxquelles je suis inscrit
        $checkInscrit = $request->query->get('sortie_inscrit');
        if ($checkInscrit) {
            $filtreInscrit = $utilisateur;
        } else {
            $filtreInscrit = null;
        }

        //Sorties auxquelles je ne suis pas inscrit
        $checkNonInscrit = $request->query->get('sortie_non_inscrit');
        if ($checkNonInscrit) {
            $filtreNonInscrit = $utilisateur;
        } else {
            $filtreNonInscrit = null;
        }

        //Sorties dont l'état est "passé"
        $checkSortiePassee = $request->query->get('sorties_passees');
        if ($checkSortiePassee) {
            $filtreSortiePassee = $etatRepository->findOneBy(['libelle' => 'Passée']);
        } else {
            $filtreSortiePassee = null;
        }


        $sorties = $paginator->paginate(
            $sortieRepository->findListOfSortiesWithFilters($filtreCampus, $filtreMot, $dateDebut, $dateFin, $filtreOrganisateur, $filtreInscrit, $filtreNonInscrit,
                $filtreSortiePassee),
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


        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $sortie->setOrganisateur($participant);
            if ($form->get('enregistrer')->isClicked()) {
                $etat = $etatRepository->findOneBy(array('libelle' => 'Créée'));
                $sortie->setEtat($etat);
                $this->em->persist($sortie);
                $this->em->flush();

                $this->flashy->success('Sortie créée !');
            } elseif ($form->get('publier')->isClicked()) {
                $etat = $etatRepository->findOneBy(array('libelle' => 'Ouverte'));
                $sortie->setEtat($etat);
                $this->em->persist($sortie);
                $this->em->flush();

                $this->flashy->success('Sortie publiée !');
            } else {
                return $this->redirectToRoute('app_sortie_index');
            }
            return $this->redirectToRoute('app_sortie_index');
        }
        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/modifier/{id<[0-9]+>}", name="app_sortie_modifier")
     */
    public function modifier(Request $request, SortieRepository $sortieRepository, $id, EtatRepository $etatRepository): Response
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
                if ($form->get('enregistrer')->isClicked()) {
                  $this->flashy->success('Sortie modifiée avec succès !');

                } elseif ($form->get('publier')->isClicked()) {
                    $etat = $etatRepository->findOneBy(array('libelle' => 'Ouverte'));
                    $sortie->setEtat($etat);

                    $this->flashy->success('Sortie publiée avec succès !');

                } elseif ($form->get('supprimer')->isClicked()) {
                    $this->em->remove($sortie);

                    $this->flashy->success('Sortie supprimée avec succès !');
                }

                $this->em->flush();
                return $this->redirectToRoute('app_sortie_index');
            }

        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !', '#');
            return $this->redirectToRoute('app_sortie_index');
        }
        return $this->render('sortie/modifier.html.twig', ['form' => $form->createView()]);
    }


    /**
     * @Route("/{id<[0-9]+>}", name="app_sortie_afficher", methods={"GET"})
     */
    public function afficher($id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        if ($sortie->getEtat()->getLibelle() !== "Créée" and $sortie->getDateHeureDebut() > new \DateTime('-1month')) {
            return $this->render('sortie/afficher.html.twig', [
                'sortie' => $sortie,
            ]);
        } else {
            $this->flashy->error('Les détails de cette sortie ne sont pas disponibles');
            return $this->redirectToRoute('app_sortie_index');
        }
    }

    /**
     * @Route("/{id}/inscription", name="app_sortie_s_inscrire", requirements={"id": "\d+"})
     */
    public function sInscrire(Sortie $sortie, ParticipantRepository $participantRepository, EtatRepository $etatRepository, SortieRepository $sortieRepository)
    {
        $now = new \DateTime('now');
        $ouvert = $etatRepository->findOneBy(array('libelle' => 'Ouverte'));
        $cloture = $etatRepository->findOneBy(array('libelle' => 'Clôturée'));
        $nbParticipantsActuels = $sortieRepository->findNumberOfParticipants($sortie);

        $participant = $participantRepository->findOneBy(['username' => $this->getUser()->getUsername()]);

        if ($sortie->getEtat() == $ouvert and $nbParticipantsActuels < $sortie->getNbInscriptionMax() and $now < $sortie->getDateLimiteInscription()) {
            $sortie->ajouterParticipant($participant);

            $this->em->flush();

            $this->flashy->success('Vous êtes bien inscrit à la sortie ' . $sortie->getNom());
        } else {
            if ($sortie->getEtat() != $ouvert) {
                $this->flashy->error('Les inscriptions sont clôturées pour cette sortie');

            } elseif ($nbParticipantsActuels > $sortie->getNbInscriptionMax()) {
                $this->flashy->error('Il n\'y a plus de places disponibles pour cette sortie');

            } elseif ($sortie->getDateLimiteInscription() > $now) {

                $sortie->setEtat($cloture);
                $this->em->flush();
                $this->flashy->error('La date limite d\'inscription pour cette sortie est dépassée' );
            }
        }

        return $this->redirectToRoute('app_sortie_index');

    }

    /**
     * @Route("/{id}/desinscription", name="app_sortie_se_desinscrire", requirements={"id": "\d+"})
     */
    public function seDesinscrire(Sortie $sortie, ParticipantRepository $participantRepository, EtatRepository $etatRepository)
    {
        $now = new \DateTime('now');
        $activiteEnCours = $etatRepository->findOneBy(array('libelle' => 'Activité en cours'));

        $participant = $participantRepository->findOneBy(['username' => $this->getUser()->getUsername()]);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée');
        }

        if ($now < $sortie->getDateHeureDebut()) {
            $sortie->enleverParticipant($participant);

            $this->flashy->success('Vous êtes bien désinscrit à la sortie ' . $sortie->getNom());

        } else {
            $sortie->setEtat($activiteEnCours);
            $this->flashy->error('La sortie est en cours, vous ne pouvez plus vous désister' );
        }
        $this->em->flush();

        return $this->redirectToRoute('app_sortie_index');

    }

    /**
     * @Route("/{id}/publier", name="app_sortie_publier", requirements={"id": "\d+"})
     */
    public function publierSortie(Sortie $sortie, EtatRepository $etatRepository)
    {
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée');
        }

        $etat = $etatRepository->findOneBy(array('libelle' => 'Ouverte'));
        $sortie->setEtat($etat);

        $this->em->flush();

        $this->flashy->success('Votre sortie a bien été publiée');
        return $this->redirectToRoute('app_sortie_index');
    }

    /**
     * @Route("/{id}/annuler", name="app_sortie_annuler", requirements={"id": "\d+"})
     */
    public function annulerSortie(Sortie $sortie, Request $request, EtatRepository $etatRepository)
    {
        $motif = $request->request->get('motif');

        $ouverte = $etatRepository->findOneBy(array('libelle' => 'Ouverte'));
        $cloture = $etatRepository->findOneBy(array('libelle' => 'Clôturée'));
        $annulee = $etatRepository->findOneBy(array('libelle' => 'Annulée'));

        if ($sortie->getEtat() == $ouverte or $sortie->getEtat() == $cloture) {
            if($motif) {
                $sortie->setInfosSortie($motif);
                $sortie->setEtat($annulee);
                $this->em->flush();

                $this->flashy->success('La sortie ' . $sortie->getNom() . ' a bien été annulée');
                return $this->redirectToRoute('app_sortie_index');
            }
        } else {
            $this->flashy->error('Vous ne pouvez plus annuler cette sortie');
            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie,
        ]);
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
