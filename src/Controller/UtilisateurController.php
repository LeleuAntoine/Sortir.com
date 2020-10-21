<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\InscriptionType;
use App\Form\ModifierProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UtilisateurController
 * @package App\Controller
 * @Route("/profil")
 */
class UtilisateurController extends AbstractController
{
    /**
     * @Route("/{id}", name="app_participant_voir_profil", requirements={"id": "\d+"})
     */
    public function voirProfil($id, ParticipantRepository $participantRepository)
    {
        $profil = $participantRepository->find($id);

        return $this->render('utilisateur/voirProfil.html.twig', [
            'profil' => $profil,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="app_participant_modifier_profil", requirements={"id": "\d+"})
     */
    public function modifierProfil(EntityManagerInterface $em, Request $request, Participant $utilisateur, UserPasswordEncoderInterface $encoder)
    {
        $profilForm = $this->createForm(ModifierProfilType::class, $utilisateur);

        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {
            $photoFile = $profilForm['photo']->getData();

            if ($photoFile) {
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads/photo_participants';

                $nomPhotoOriginal = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $nouveauNomPhoto = Urlizer::urlize($nomPhotoOriginal).'-'.uniqid().'.'.$photoFile->guessExtension();

                $photoFile->move($destination, $nouveauNomPhoto);

                $utilisateur->setPhoto($nouveauNomPhoto);
            }

            $hashed = $encoder->encodePassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($hashed);

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
