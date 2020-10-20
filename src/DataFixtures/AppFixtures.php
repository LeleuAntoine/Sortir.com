<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use function Symfony\Component\String\u;

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

        $villes = array();
        for ($i = 1; $i <= 4; $i++) {
            $villes[$i] = new Ville();
            switch ($i) {
                case 1:
                    $villes[$i]->setNom('Quimper');
                    $villes[$i]->setCodePostal('29000');
                    break;
                case 2:
                    $villes[$i]->setNom('Chartres-de-Bretagne');
                    $villes[$i]->setCodePostal('35131');
                    break;
                case 3:
                    $villes[$i]->setNom('Saint-Herblain');
                    $villes[$i]->setCodePostal('44800');
                    break;
                case 4:
                    $villes[$i]->setNom('Niort');
                    $villes[$i]->setCodePostal('79000');
                    break;

            }
            $manager->persist($villes[$i]);
        }

        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i <= 12; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->sentence(6));
            $lieu->setVille($villes[$faker->numberBetween(1, 4)]);
            $lieu->setRue($faker->address);
            $lieu->setLatitude($faker->latitude(46, 48));
            $lieu->setLongitude($faker->longitude(-1, 4));
            $lieu->setSortie(null);
            $manager->persist($lieu);
        }

        $admin = new Participant();
        $admin->setUsername('admin');
        $admin->setNom($faker->lastName);
        $admin->setPrenom($faker->firstName);
        $admin->setMail('admin_eni@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCampus($campus[3]);
        $admin->setTelephone($faker->phoneNumber);
        $admin->setActif(true);
        $admin->setPhoto($faker->imageUrl(200, 200, 'people'));

        $password = $this->encoder->encodePassword($admin, 'azerty');
        $admin->setPassword($password);

        $manager->persist($admin);

        for ($i = 1; $i <= 10; $i++) {
            $utilisateur = new Participant();
            $utilisateur->setUsername($faker->userName);
            $utilisateur->setNom($faker->lastName);
            $utilisateur->setPrenom($faker->firstName);
            $utilisateur->setMail($faker->email);
            $utilisateur->setCampus($campus[$faker->numberBetween(1, 4)]);
            $utilisateur->setTelephone($faker->phoneNumber);
            $utilisateur->setActif($faker->boolean(50));
            $utilisateur->setPhoto($faker->imageUrl(200, 200, 'people'));
            $utilisateur->setPassword($password);
            $manager->persist($utilisateur);
        }

        $manager->flush();
    }

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
}
