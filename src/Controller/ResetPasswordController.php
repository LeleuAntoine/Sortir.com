<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ChangePasswordFormType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    private $em;
    private $flashy;

    public function __construct(EntityManagerInterface $em, FlashyNotifier $flashy)
    {
        $this->em = $em;
        $this->flashy = $flashy;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="app_forgot_password_request")
     */
    public function request(Request $request, ParticipantRepository $participantRepository): Response
    {
        $utilisateur = $participantRepository->findOneBy(['mail' => $request->request->get('mail')]);
        if ($utilisateur) {
            /*$this->container->get('session')->set('utilisateur', $utilisateur);*/
            return $this->redirectToRoute('app_reset_password', ['id' => $utilisateur->getId()]);
        } elseif ($request->isMethod('post')) {
            $this->flashy->error('Cette adresse mail ne correspond à aucun utilisateur');
            $this->redirectToRoute('app_sortie_index');
        }


        return $this->render('reset_password/request.html.twig');
    }

    /**
     * @Route("/reset/{id}", name="app_reset_password", requirements={"id": "\d+"})
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, Participant $utilisateur): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Encode the plain password, and set it.
            $encodedPassword = $passwordEncoder->encodePassword(
                $utilisateur,
                $form->get('plainPassword')->getData()
            );

            $utilisateur->setPassword($encodedPassword);
            $this->em->flush();

            $this->flashy->success('Votre mot de passe a bien été réinitialisé !');
            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
