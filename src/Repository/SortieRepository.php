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

    public function findListOfSortiesWithFilters($campus = '', $mot = '')
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect('c');
        if ($campus != '' and $mot != '') {
            $qb->where('c = :campus')
                ->setParameter('campus', $campus)
                ->andWhere('lower(s.nom) LIKE lower(:mot)')
                ->setParameter('mot', '%' . $mot . '%');

        } else if ($mot != '') {
            $qb->where('lower(s.nom) LIKE lower(:mot)')
                ->setParameter('mot', '%' . $mot . '%');
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
