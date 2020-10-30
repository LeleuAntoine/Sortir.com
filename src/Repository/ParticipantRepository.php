<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

   public function findParticipantsWithFilters($campus = '', $nom = '', $prenom = '', $actif = null)
   {
       $qb = $this->createQueryBuilder('p')
           ->addSelect('c');
       if ($campus != '') {
           $qb->andWhere('c = :campus')
               ->setParameter('campus', $campus);
       }
       if ($nom != '') {
           $qb->andWhere('lower(p.nom) LIKE lower(:nom)')
               ->setParameter('nom', '%' . $nom . '%');
       }
       if ($prenom != '') {
           $qb->andWhere('lower(p.prenom) LIKE lower(:prenom)')
               ->setParameter('prenom', '%' . $prenom . '%');
       }
       if ($actif === 'actif') {
           $qb->andWhere('p.actif = :actif')
               ->setParameter('actif', true);
       }
       if ($actif === 'non_actif') {
           $qb->andWhere('p.actif = :actif')
               ->setParameter('actif', false);
       }
       $qb->join('p.campus', 'c')
           ->orderBy('p.nom');

       return $qb;
   }
}
