<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{

    /**
     * @Route("/ville", name="ville")
     */
    public function index(VilleRepository $villeRepository, FlashyNotifier $flashy,
                          PaginatorInterface $paginator, Request $request,
                          EntityManagerInterface $em)
    {
        $utilisateur = $this->getUser();

        if(in_array("ROLE_ADMIN", $utilisateur->getRoles())) {

            $villes = $villeRepository->findAll();
            $filtreMot = $request->query->get('nom_ville_contient');
            $ajoutNomVille = $request->query->get('nom_ville');
            $ajoutCodePostalVille = $request->query->get('code_postal_ville');

            if ($ajoutNomVille and $ajoutCodePostalVille) {
                $nouvelleVille = new Ville();
                $nouvelleVille->setNom($ajoutNomVille);
                $nouvelleVille->setCodePostal($ajoutCodePostalVille);
                $em->persist($nouvelleVille);
                $em->flush();
            }

            $villes = $paginator->paginate(
                $villeRepository->trouverVilleAvecFiltre($filtreMot),
                $request->query->getInt('page', 1),
                5
            );

            return $this->render('ville/index.html.twig', [
                'villes' => $villes,
            ]);
        } else {
            $flashy->error('Vous ne disposez pas des droits nécessaire !' );
            return $this->redirectToRoute('app_sortie_index');
        }
    }



}
