<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\InscriptionType;
use App\Form\ModifierProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_participant_inscription")
     */
    public function sInscrire(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $utilisateur = new Participant();
        $inscriptionForm = $this->createForm(InscriptionType::class, $utilisateur);

        $inscriptionForm->handleRequest($request);
        if ($inscriptionForm->isSubmitted() && $inscriptionForm->isValid()) {
            $utilisateur->setActif(true);

            $hashed = $encoder->encodePassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($hashed);

            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Votre compte a bien été créé !');
            return $this->redirectToRoute('app_participant_connexion');
        }

        return $this->render('utilisateur/inscription.html.twig', [
            'inscriptionForm' => $inscriptionForm->createView(),
        ]);

    }

    /**
     * @Route("/connexion", name="app_participant_connexion")
     */
    public function seConnecter()
    {
        return $this->render('utilisateur/connexion.html.twig', []);
    }

    /**
     * @Route("/deconnexion", name="app_participant_deconnexion")
     */
    public function seDeconnecter(){}

    /**
     * @Route("/profil/{id}", name="app_participant_voir_profil", requirements={"id": "\d+"})
     */
    public function voirProfil($id, ParticipantRepository $participantRepository)
    {
        $profil = $participantRepository->find($id);

        return $this->render('utilisateur/voirProfil.html.twig', [
            'profil' => $profil,
        ]);
    }

    /**
     * @Route("/profil/{id}/modifier", name="app_participant_modifier_profil", requirements={"id": "\d+"})
     */
    public function modifierProfil(EntityManagerInterface $em, Request $request, Participant $utilisateur)
    {
        $profilForm = $this->createForm(ModifierProfilType::class, $utilisateur);

        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {
            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Votre profil a été modifié avec succès');
            return $this->redirectToRoute('app_participant_voir_profil', ['id' => $utilisateur->getId()]);
        }

        return $this->render('utilisateur/modifierProfil.html.twig', [
            'profilForm' => $profilForm->createView(),
        ]);
    }
}
