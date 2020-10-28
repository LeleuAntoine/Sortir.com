<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\InscriptionType;
use App\Form\ModifierProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function modifierProfil(EntityManagerInterface $em, Request $request,
                                   Participant $participant, UserPasswordEncoderInterface $encoder)
    {
        if ($participant->getUsername() === $this->getUser()->getUsername()) {

            $profilForm = $this->createForm(ModifierProfilType::class, $participant);
            $profilForm->handleRequest($request);

            if ($profilForm->isSubmitted() && $profilForm->isValid()) {
                $photoFile = $profilForm['photo']->getData();

                if ($photoFile) {
                    $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/photo_participants';

                    $nomPhotoOriginal = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $nouveauNomPhoto = Urlizer::urlize($nomPhotoOriginal) . '-' . uniqid() . '.' . $photoFile->guessExtension();

                    $photoFile->move($destination, $nouveauNomPhoto);
                } else {
                    $nouveauNomPhoto = 'profile-picture-5f91a9bb0a4bf.png';
                }
                $participant->setPhoto($nouveauNomPhoto);

                $hashed = $encoder->encodePassword($participant, $participant->getPassword());
                $participant->setPassword($hashed);

                $em->flush();

                $this->addFlash('success', 'Votre profil a été modifié avec succès');
                return $this->redirectToRoute('app_participant_voir_profil', ['id' => $participant->getId()]);
            }

            return $this->render('utilisateur/modifierProfil.html.twig', [
                'profilForm' => $profilForm->createView(),
            ]);
        } else {
            $this->addFlash('erreur', 'Vous ne disposez pas des droits nécessaire !');
            return $this->redirectToRoute('app_sortie_index');
        }
    }
}
