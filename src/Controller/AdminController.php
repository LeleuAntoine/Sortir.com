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
    public function index(ParticipantRepository $participantRepository, PaginatorInterface $paginator, Request $request)
    {
        if ($this->isGranted("ROLE_ADMIN")) {
            $participants = $paginator->paginate(
                $participantRepository->findAll(),
                $request->query->getInt('page', 1),
                10
            );

            return $this->render('admin/index.html.twig', [
                'participants' => $participants,
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
        $participant = new Participant();

        $form = $this->createForm(NouvelUtilisateurType::class, $participant);

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $this->em->persist($participant);
            $this->em->flush();


            $this->flashy->success('Utilisateur ajouté !');
            return $this->redirectToRoute('app_admin_index');
        }

        return $this->render('admin/ajouterUtilisateur.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ajouterFichierUtilisateurs", name="app_admin_ajouter_fichier_utilisateurs")
     */
    public function ajouterFichierUtilisateur(Request $request, CampusRepository $campusRepository, UserPasswordEncoderInterface $encoder) {

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

        return $this->render('admin/ajouterFichierUtilisateurs.html.twig');
    }
}
