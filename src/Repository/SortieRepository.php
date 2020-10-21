<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findListOfSortiesWithCampus($filter = '')
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect('c');
        if ($filter != '') {
            $qb->where('c = :filter')
                ->setParameter('filter', $filter);
        }
        $qb->join('s.siteOrganisateur', 'c')
            ->orderBy('s.dateHeureDebut', 'ASC')
        ;

        return $qb;
    }

    public function findParticipants($id)
    {
        return $qb = $this->createQueryBuilder('s')
            ->addSelect('p')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->join('s.participants', 'p')
            ->getQuery()
            ->getResult()
        ;
    }
}
