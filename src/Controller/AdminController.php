<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\NouvelUtilisateurType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Reader;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $em;
    private $flashy;

    public function __construct(FlashyNotifier $flashy, EntityManagerInterface $em)
    {
        $this->flashy = $flashy;
        $this->em = $em;
    }

    /**
     * @Route("/", name="app_admin_index")
     */
    public function index(CampusRepository $campusRepository, ParticipantRepository $participantRepository, PaginatorInterface $paginator, Request $request)
    {
        if ($this->isGranted("ROLE_ADMIN")) {
            $campus = $campusRepository->findAll();

            //Utilisateur selon campus
            $filtreCampus = $request->query->get('campus');

            //Utilisateur selon nom
            $filtreNom = $request->query->get('nom_utilisateur_contient');

            //Utilisateur selon prénom
            $filtrePrenom = $request->query->get('prenom_utilisateur_contient');

            //Utilisateurs actifs
            $filtreActif = $request->query->get('utilisateurs_actifs');

            //Utilisateurs non actifs


            $participants = $paginator->paginate(
                $participantRepository->findParticipantsWithFilters($filtreCampus, $filtreNom, $filtrePrenom, $filtreActif),
                $request->query->getInt('page', 1),
                10
            );

            return $this->render('admin/index.html.twig', [
                'participants' => $participants,
                'campus' => $campus,
            ]);

        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !', '#');
            return $this->redirectToRoute('app_sortie_index');
        }
    }

    /**
     * @Route("/ajouterUtilisateur", name="app_admin_ajouter_utilisateur")
     */
    public function ajouterUtilisateur(Request $request)
    {
        if ($this->isGranted("ROLE_ADMIN")) {
            $participant = new Participant();

            $form = $this->createForm(NouvelUtilisateurType::class, $participant);

            $form->handleRequest($request);

            if ($form->isSubmitted() and $form->isValid()) {
                $this->em->persist($participant);
                $this->em->flush();


                $this->flashy->success('Utilisateur ajouté !');
                return $this->redirectToRoute('app_admin_index');
            }

        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !', '#');
            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('admin/ajouterUtilisateur.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ajouterFichierUtilisateurs", name="app_admin_ajouter_fichier_utilisateurs")
     */
    public function ajouterFichierUtilisateur(Request $request, CampusRepository $campusRepository, UserPasswordEncoderInterface $encoder) {

        if ($this->isGranted("ROLE_ADMIN")) {
            $fichier = $request->files->get('inserer_fichier_utilisateurs');

            if ($fichier) {
                $reader = Reader::createFromPath($fichier);
                $reader->setHeaderOffset(0);

                foreach ($reader->getRecords() as $ligne) {
                    $utilisateur = new Participant();
                    $utilisateur->setNom($ligne['nom']);
                    $utilisateur->setPrenom($ligne['prenom']);
                    $utilisateur->setUsername($ligne['username']);
                    $utilisateur->setTelephone($ligne['telephone']);
                    $utilisateur->setMail($ligne['mail']);
                    $utilisateur->setPassword($ligne['password']);

                    $hashed = $encoder->encodePassword($utilisateur, $utilisateur->getPassword());
                    $utilisateur->setPassword($hashed);

                    $utilisateur->setActif($ligne['actif']);

                    $campus = $campusRepository->findOneBy(['nom' => $ligne['campus']]);

                    $utilisateur->setCampus($campus);

                    $this->em->persist($utilisateur);
                }

                $this->em->flush();

                $this->flashy->success('Les données de votre fichier ont bien été ajoutées');
                return $this->redirectToRoute('app_admin_index');
            }

        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !', '#');
            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('admin/ajouterFichierUtilisateurs.html.twig');
    }

    /**
     * @Route("/desactiver/{id}", name="app_admin_desactiver_utilisateur", requirements={"id": "\d+"})
     */
    public function desactiverUtilisateur(Participant $utilisateur)
    {
        if ($this->isGranted("ROLE_ADMIN")) {
            $utilisateur->setActif(false);

            $this->em->flush();

            $this->flashy->success('Cet utilisateur a bien été désactivé');
            return $this->redirectToRoute('app_admin_index');

        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !', '#');
            return $this->redirectToRoute('app_sortie_index');
        }
    }

    /**
     * @Route("/activer/{id}", name="app_admin_activer_utilisateur", requirements={"id": "\d+"})
     */
    public function activerUtilisateur(Participant $utilisateur)
    {
        if ($this->isGranted("ROLE_ADMIN")) {
            $utilisateur->setActif(true);

            $this->em->flush();

            $this->flashy->success('Cet utilisateur a bien été activé');
            return $this->redirectToRoute('app_admin_index');

        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !', '#');
            return $this->redirectToRoute('app_sortie_index');
        }
    }
    /**
     * @Route("/supprimer/{id}", name="app_admin_supprimer_utilisateur", requirements={"id": "\d+"})
     */
    public function supprimerUtilisateur(Participant $utilisateur)
    {
        if ($this->isGranted("ROLE_ADMIN")) {
            if (empty($utilisateur->getSortiesOrganisees()->getValues())) {
                $this->em->remove($utilisateur);
                $this->em->flush();


                $this->flashy->success('Cet utilisateur a bien été supprimé');
                return $this->redirectToRoute('app_admin_index');

            } else {
                $utilisateur->setActif(false);
                $this->em->flush();

                $this->flashy->info('Cet utilisateur ne peut pas être supprimé, il a été désactivé');
                return $this->redirectToRoute('app_admin_index');
            }

        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !', '#');
            return $this->redirectToRoute('app_sortie_index');
        }
    }

}
