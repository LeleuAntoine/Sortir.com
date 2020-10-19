<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function load(ObjectManager $manager)
    {
        $etats = array();
        for ($i = 1; $i <= 6; $i++) {
            $etats[$i] = new Etat();
            switch ($i) {
                case 1:
                    $etats[$i]->setLibelle('Créée');
                    break;
                case 2:
                    $etats[$i]->setLibelle('Ouverte');
                    break;
                case 3:
                    $etats[$i]->setLibelle('Clôturée');
                    break;
                case 4:
                    $etats[$i]->setLibelle('Activité en cours');
                    break;
                case 5:
                    $etats[$i]->setLibelle('Passée');
                    break;
                case 6:
                    $etats[$i]->setLibelle('Annulée');
                    break;
            }
            $manager->persist($etats[$i]);
        }

        $campus = array();
        for ($i = 1; $i <=4; $i++) {
            $campus[$i] = new Campus();
            switch ($i) {
                case 1:
                    $campus[$i]->setNom('Quimper');
                    break;
                case 2:
                    $campus[$i]->setNom('Chartres-de-Bretagne');
                    break;
                case 3:
                    $campus[$i]->setNom('Saint-Herblain');
                    break;
                case 4:
                    $campus[$i]->setNom('Niort');
            }
            $manager->persist($campus[$i]);
        }

        $faker = \Faker\Factory::create('fr_FR');

        $admin = new Participant();
        $admin->setUsername('admin');
        $admin->setNom($faker->lastName);
        $admin->setPrenom($faker->firstName);
        $admin->setMail('admin_eni@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCampus($campus[3]);
        $admin->setTelephone($faker->phoneNumber);
        $admin->setActif(true);

        $password = $this->encoder->encodePassword($admin, 'azerty');
        $admin->setPassword($password);

        $manager->persist($admin);

        $manager->flush();
    }

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
}
