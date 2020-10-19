<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CampusRepository::class)
 */
class Campus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Participant", mappedBy="campus")
     */
    private $stagiaires;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sortie", mappedBy="siteOrganisateur")
     */
    private $sortiesOrganisees;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param mixed $nom
     */
    public function setNom($nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return mixed
     */
    public function getStagiaires()
    {
        return $this->stagiaires;
    }

    /**
     * @param mixed $stagiaires
     */
    public function setStagiaires($stagiaires): void
    {
        $this->stagiaires = $stagiaires;
    }

    /**
     * @return mixed
     */
    public function getSortiesOrganisees()
    {
        return $this->sortiesOrganisees;
    }

    /**
     * @param mixed $sortiesOrganisees
     */
    public function setSortiesOrganisees($sortiesOrganisees): void
    {
        $this->sortiesOrganisees = $sortiesOrganisees;
    }


}
