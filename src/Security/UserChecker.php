<?php

namespace App\Security;

use App\Entity\Participant as Utilisateur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Utilisateur) {
        return;
    }

        if (!$user->getActif()) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été désactivé, vous ne pouvez plus vous connecter.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {

    }
}