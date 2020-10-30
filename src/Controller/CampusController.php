<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/campus")
 */
class CampusController extends AbstractController
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
     * @Route("", name="app_campus_index")
     */
    public function index(CampusRepository $campusRepository, PaginatorInterface $paginator,
                          Request $request)
    {
        $campus = new Campus();
        $utilisateur = $this->getUser();

        if (in_array("ROLE_ADMIN", $utilisateur->getRoles())) {
            $filtreMot = $request->query->get('nom_campus_contient');

            $form = $this->createForm(CampusType::class, $campus);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $exist = true;
                $campusTab = $campusRepository->findAll();

                foreach ($campusTab as $c) {
                    if ($campus->getNom() === $c->getNom())
                    {
                        $exist = false;
                        $this->flashy->error('Ce campus existe déjà');
                    }
                }
                if ($exist) {
                    $this->em->persist($campus);
                    $this->em->flush();
                    $this->flashy->success('Campus créé avec succès !');
                }
            }
            $campusTab = $paginator->paginate(
                $campusRepository->trouverCampusAvecFiltre($filtreMot),
                $request->query->getInt('page', 1),
                5
            );

            return $this->render('campus/index.html.twig', [
                'campusTab' => $campusTab,
                'form' => $form->createView()
            ]);
        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !');
            return $this->redirectToRoute('app_sortie_index');
        }
    }

    /**
     * @Route("/{id}/supprimer", name="app_campus_supprimer", requirements={"id": "\d+"})
     */
    public function supprimerCampus(Campus $campus, ParticipantRepository $participantRepository)
    {
        $utilisateur = $this->getUser();

        if (in_array("ROLE_ADMIN", $utilisateur->getRoles())) {
            $participant = $participantRepository->findOneBy(array('campus' => $campus));
            if (empty($participant)) {
                $this->em->remove($campus);
                $this->em->flush();
                $this->flashy->success('Campus supprimé avec succès !');
            } else {
                $this->flashy->error('Ce campus ne peut pas être supprimé');
            }
            return $this->redirectToRoute('app_campus_index');
        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !');
            return $this->redirectToRoute('app_sortie_index');
        }
    }

}
