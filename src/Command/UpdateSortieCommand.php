<?php


namespace App\Command;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSortieCommand extends Command
{
    private $sortieRepository;
    private $etatRepository;
    private $em;

    protected function configure()
    {
        $this
            ->setName('app:update-sortie')
            ->setDescription('Mise à jour de l\'état des sorties');
    }

    public function __construct(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sorties = $this->sortieRepository->findAll();
        $etatCloture = $this->etatRepository->findOneBy(array('libelle' => 'Clôturée'));
        $etatEnCours = $this->etatRepository->findOneBy(array('libelle' => 'Activité en cours'));
        $etatPassee = $this->etatRepository->findOneBy(array('libelle' => 'Passée'));

        foreach ($sorties as $sortie) {
            $dateJour = new \DateTime('now');
            $stringDateJour = strtotime($dateJour->format('d-m-Y H:i'));

            $dateLimite = strtotime($sortie->getDateLimiteInscription()->format('d-m-Y H:i'));
            $dateDebut = strtotime($sortie->getDateHeureDebut()->format('d-m-Y H:i'));

            $dateDebutPlusDuree = strtotime(date("d-m-Y H:i", strtotime($sortie->getDateHeureDebut()->format('d-m-Y H:i') . "+{$sortie->getDuree()}  minutes")));

            $nom = $sortie->getNom();
            if ($sortie->getEtat()->getLibelle() !== 'Créée'
                and $sortie->getEtat()->getLibelle() !== 'Passée'
                and $sortie->getEtat()->getLibelle() !== 'Annulée') {
                if ($stringDateJour >= $dateLimite and $stringDateJour < $dateDebut) {
                    $sortie->setEtat($etatCloture);
                    $output->writeln("$nom : Clôturée");
                }
                if ($stringDateJour >= $dateDebut and $stringDateJour <= $dateDebutPlusDuree) {
                    $sortie->setEtat($etatEnCours);
                    $output->writeln("$nom : Activité en cours");
                }
                if ($stringDateJour > $dateDebutPlusDuree) {
                    $sortie->setEtat($etatPassee);
                    $output->writeln("$nom : Passée");
                }
            }
        }
        $this->em->flush();

        $output->writeln("Commande Réussie");

        return Command::SUCCESS;
    }
}