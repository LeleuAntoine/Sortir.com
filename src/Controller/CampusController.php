<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;

class CampusController extends AbstractController
{
    /**
     * @Route("/campus", name="campus")
     */
    public function index(CampusRepository $campusRepository, FlashyNotifier $flashy,
                          PaginatorInterface $paginator, Request $request,
                          EntityManagerInterface $em)
    {
        $utilisateur = $this->getUser();

        if (in_array("ROLE_ADMIN", $utilisateur->getRoles())) {

            $filtreMot = $request->query->get('nom_campus_contient');
            $ajoutNomCampus = $request->query->get('nom_campus');


            dump($ajoutNomCampus);
            if ($request->query->get('ajouter')) {
                $nouveauCampus = new Campus();
                $nouveauCampus->setNom($ajoutNomCampus);
                $em->persist($nouveauCampus);
                $em->flush();
            }

            $campusTab = $paginator->paginate(
                $campusRepository->trouverCampusAvecFiltre($filtreMot),
                $request->query->getInt('page', 1),
                5
            );

            return $this->render('campus/index.html.twig', [
                'campusTab' => $campusTab,
            ]);
        } else {
            $flashy->error('Vous ne disposez pas des droits nécessaire !');
            return $this->redirectToRoute('app_sortie_index');
        }
    }

}
