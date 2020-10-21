<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Date;
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

        $lieux = array();
        for ($i = 0; $i <= 12; $i++) {
            $lieux[$i] = new Lieu();
            $lieux[$i]->setNom($faker->sentence(6));
            $lieux[$i]->setVille($villes[$faker->numberBetween(1, 4)]);
            $lieux[$i]->setRue($faker->streetAddress);
            $lieux[$i]->setLatitude($faker->latitude(46, 48));
            $lieux[$i]->setLongitude($faker->longitude(-1, 4));
            $lieux[$i]->setSorties(null);
            $manager->persist($lieux[$i]);
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

        $utilisateurs = array();
        for ($i = 1; $i <= 10; $i++) {
            $utilisateurs[$i] = new Participant();
            $utilisateurs[$i]->setUsername($faker->userName);
            $utilisateurs[$i]->setNom($faker->lastName);
            $utilisateurs[$i]->setPrenom($faker->firstName);
            $utilisateurs[$i]->setMail($faker->email);
            $utilisateurs[$i]->setCampus($campus[$faker->numberBetween(1, 4)]);
            $utilisateurs[$i]->setTelephone($faker->phoneNumber);
            $utilisateurs[$i]->setActif($faker->boolean(50));
            $utilisateurs[$i]->setPhoto($faker->imageUrl(200, 200, 'people'));
            $utilisateurs[$i]->setPassword($password);
            $manager->persist($utilisateurs[$i]);
        }

        /*//sorties
        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence(6));
            $sortie->setDateHeureDebut($faker->dateTimeBetween('-1 month', '+1 month'));
            $sortie->setDuree($faker->numberBetween(30, 120));
            $sortie->setDateLimiteInscription($faker->dateTimeBetween('-1 month', $sortie->getDateHeureDebut()));
            $sortie->setNbInscriptionMax($faker->numberBetween(5, 50));
            $description = '<p>' . join($faker->paragraphs(3), '</p><p>') .'</p>';
            $sortie->setInfosSortie($description);
            $sortie->setOrganisateur($utilisateurs[$faker->numberBetween(1, 10)]);
            $sortie->setParticipants(null);
            $sortie->setSiteOrganisateur($campus[$faker->numberBetween(1, 4)]);
            $sortie->setLieu($lieux[$faker->numberBetween(1, 12)]);

            $now = new \DateTime();
            try {
                $duree = $now->add(new DateInterval('PT' . $sortie->getDuree() . 'M'));
            } catch (\Exception $e) {
            }
            if($sortie->getDateLimiteInscription() > $now) {
                $sortie->setEtat($etats[2]);
            } elseif ($sortie->getDateLimiteInscription() < $now && $sortie->getDateHeureDebut() > $now) {
                $sortie->setEtat($etats[3]);
            } elseif ($sortie->getDateHeureDebut() < $now && $sortie->getDateHeureDebut() < $duree) {
                $sortie->setEtat($etats[4]);
            } else {
                $sortie->setEtat($etats[5]);
            }

            $manager->persist($sortie);

        }*/

        $manager->flush();
    }

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
}
