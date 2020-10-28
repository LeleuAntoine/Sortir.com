<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\NouvelUtilisateurType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
}
