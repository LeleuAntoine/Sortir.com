<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
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
    /**
     * @Route("/", name="sortie")
     */
    public function index(SortieRepository $sortieRepository, CampusRepository $campusRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $campus = $campusRepository->findAll();

        $filtreCampus = $request->query->get('campus');
        $filtreMot = $request->query->get('nom_sortie_contient');

        $sorties = $paginator->paginate(
            $sortieRepository->findListOfSortiesWithFilters($filtreCampus, $filtreMot),
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
    public function creer(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie;

        $form = $this->createForm(SortieType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
//            $lieu = $form->getData();
//            $sortie->setLieux($lieu);
            $sortie = $form->getData();
//            $sortie->setLieux($data['lieu']);
//            $sortie->setEtat(1);
            $em->persist($sortie);
            $em->flush();

//            return $this->redirectToRoute('');
        }
        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/modifier", name="app_sortie_modifier", methods={"GET", "POST"})
     */
    public function modifier(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie;

        $form = $this->createForm(SortieType::class);
        $form->handleRequest($request);

//        if ($form->isSubmitted() && $form->isValid()) {
//            $data = $form->getData();
//            $sortie->setNom($data['nom']);
//            $sortie->setOrganisateur($data['organisateur']);
//            $sortie->setDateHeureDebut($data['dateHeureDebut']);
//            $sortie->setDateLimiteInscription($data['dateLimiteInscription']);
//            $sortie->setNbInscriptionMax($data['nbInscriptionMax']);
//            $sortie->setDuree($data['duree']);
//            $sortie->setInfosSortie($data['infosSortie']);
//            $sortie->setLieux($data['lieux']);
//            $sortie->setEtat($data['etat']);
//            $em->persist($sortie);
//            $em->flush();
//
//            return $this->redirectToRoute('');
//        }
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
