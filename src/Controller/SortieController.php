<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Class SortieController
 * @package App\Controller
 * @Route("/sortie")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("/", name="app_sortie_index")
     */
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findBy([], ['dateHeureDebut' => 'DESC']);
        return $this->render('sortie/index.html.twig', ['sorties' => $sorties]);
    }

    /**
     * @Route ("/creer", name="app_sortie_creer", methods={"GET", "POST"})
     */
    public function creer(Request $request, EntityManagerInterface $em, Security $security, ParticipantRepository $participantRepository): Response
    {
        $sortie = new Sortie;
        $user = $participantRepository->findOneBy(array('username' => $security->getUser()->getUsername()));
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie = $form->getData();
//            $sortie->setEtat(1);
            $sortie->setOrganisateur($user);
            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('app_sortie_index');
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
    public function afficher(Sortie $sortie): Response
    {
        return $this->render('sortie/afficher.html.twig', compact('sortie'));
    }

}
