<?php

namespace App\Repository;

use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }

    /**
    * @return \Doctrine\ORM\QueryBuilder Returns an array of Ville objects
    */
    public function trouverVilleAvecFiltre($mot = '')
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect();
            if($mot != ''){
                $qb->andWhere('lower(s.nom) LIKE lower(:mot)')
                    ->setParameter('mot', '%' . $mot . '%');
            }
            $qb->orderBy('s.nom', 'ASC');
            return $qb;
    }
}
