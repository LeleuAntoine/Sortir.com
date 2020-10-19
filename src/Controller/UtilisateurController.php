<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render('utilisateur/login.html.twig', []);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){}
}
