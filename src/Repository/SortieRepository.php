<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

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

    public function findListOfSortiesWithFilters($campus = '', $mot = '', $debutPeriode = '', $finPeriode = '', $organisateur = '', $inscrit = '', $nonInscrit = '', $sortiePassee = '')
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect('c');
        if ($campus != '') {
            $qb->andWhere('c = :campus')
                ->setParameter('campus', $campus);
        }
        if ($mot != '') {
            $qb->andWhere('lower(s.nom) LIKE lower(:mot)')
                ->setParameter('mot', '%' . $mot . '%');
        }
        if ($debutPeriode != '' and $finPeriode != '') {
            $qb->andWhere($qb->expr()->between('s.dateHeureDebut', ':dateDebut', ':dateFin'))
                ->setParameter('dateDebut', $debutPeriode)
                ->setParameter('dateFin', $finPeriode);
        }
        if ($organisateur != '') {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $organisateur->getId());
        }
        if ($inscrit != '') {
            $qb->andWhere('p = :inscrit')
                ->setParameter('inscrit', $inscrit->getId())
                ->join('s.participants', 'p');
        }
        if ($nonInscrit != '') {
            $sub = $this->createQueryBuilder('sortie')
                ->select('sortie.id')
                ->where('participants = :nonInscrit')
                ->join('sortie.participants', 'participants');
            $qb->andWhere($qb->expr()->notIn('s.id', $sub->getDQL()))
                ->setParameter('nonInscrit', $nonInscrit->getId());

        }
        if ($sortiePassee != '') {
            $qb->andWhere('s.etat = :passee')
                ->setParameter('passee', $sortiePassee->getId());
        }
        $qb->join('s.siteOrganisateur', 'c')
            ->orderBy('s.dateHeureDebut', 'ASC');

        return $qb;
    }

    public function findNumberOfParticipants($sortie)
    {
        try {
            return $qb = $this->createQueryBuilder('s')
                ->select('count(p)')
                ->join('s.participants', 'p')
                ->where('s.id = :sortie_id')
                ->setParameter('sortie_id', $sortie->getId())
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        }
    }

}
