<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findBy([], ['dateHeureDebut' => 'DESC']);
        return $this->render('sortie/index.html.twig', ['sorties' => $sorties]);
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
            $sortie->setNom($data['nom']);
            $sortie->setOrganisateur(1);
            $sortie->setDateHeureDebut($data['datedebut']);
            $sortie->setDateLimiteInscription($data['datecloture']);
            $sortie->setNbInscriptionMax($data['nbinscriptionsmax']);
            $sortie->setDuree($data['duree']);
            $sortie->setInfosSortie($data['descriptioninfos']);
            $sortie->setLieux($data['nom_lieu']);
            $sortie->setEtat(1);
            $em->persist($sortie);
            $em->flush();

//            return $this->redirectToRoute('');
        }
        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id<[0-9]+>}", name="app_sortie_afficher", methods={"GET"})
     */
    public function afficher(Sortie $sortie): Response
    {
        return $this->render('sortie/afficher.html.twig', compact('sortie'));
    }

}
