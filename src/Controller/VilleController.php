<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
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
     * @Route("/ville", name="ville")
     */
    public function index(VilleRepository $villeRepository, PaginatorInterface $paginator,
                          Request $request)
    {
        $ville = new Ville();
        $utilisateur = $this->getUser();

        if (in_array("ROLE_ADMIN", $utilisateur->getRoles())) {
            $filtreMot = $request->query->get('nom_ville_contient');

            $form = $this->createForm(VilleType::class, $ville);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $exist = true;
                $villes = $villeRepository->findAll();

                foreach ($villes as $v) {
                    if ($ville->getNom() === $v->getNom() and
                        $ville->getCodePostal() === $v->getCodePostal()) {
                        $exist = false;
                        $this->flashy->error('Cette ville éxiste déjà');
                    }
                }
                if ($exist) {
                    $this->em->persist($ville);
                    $this->em->flush();
                    $this->flashy->success('Ville crée avec succé !');
                }
            }

            $villes = $paginator->paginate(
                $villeRepository->trouverVilleAvecFiltre($filtreMot),
                $request->query->getInt('page', 1),
                5
            );

            return $this->render('ville/index.html.twig', [
                'villes' => $villes,
                'form' => $form->createView()
            ]);

        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !');
            return $this->redirectToRoute('app_sortie_index');
        }

    }

    /**
     * @Route("/{id}/supprimer", name="app_ville_supprime", requirements={"id": "\d+"})
     */
    public function supprimerVille(Ville $ville, LieuRepository $lieuRepository)
    {
        $utilisateur = $this->getUser();

        if (in_array("ROLE_ADMIN", $utilisateur->getRoles())) {
            $lieu = $lieuRepository->findOneBy(array('ville' => $ville));
            if (empty($lieu)) {
                $this->em->remove($ville);
                $this->em->flush();
                $this->flashy->success('Ville supprimée avec succès !');
            } else {
                $this->flashy->error('Cette ville ne peut pas être supprimé');
            }
            return $this->redirectToRoute('ville');
        } else {
            $this->flashy->error('Vous ne disposez pas des droits nécessaires !');
            return $this->redirectToRoute('app_sortie_index');
        }
    }
}
