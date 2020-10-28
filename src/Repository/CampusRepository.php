<?php

namespace App\Repository;

use App\Entity\Campus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Campus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campus[]    findAll()
 * @method Campus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder Returns an array of Ville objects
     */
    public function trouverCampusAvecFiltre($mot = '')
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect();
        if ($mot != '') {
            $qb->andWhere('lower(s.nom) LIKE lower(:mot)')
                ->setParameter('mot', '%' . $mot . '%');
        }
        $qb->orderBy('s.nom', 'ASC');
        return $qb;
    }
}
