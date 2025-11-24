<?php

namespace App\Repository;

use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tache>
 */
class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    /**
     * Retourne toutes les tâches entre deux dates (incluses)
     */
    public function findBetweenDates(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.assignerA', 'u')->addSelect('u')
            ->leftJoin('t.pole', 'p')->addSelect('p')
            ->leftJoin('t.createdBy', 'c')->addSelect('c')
            ->where('t.dateExecution BETWEEN :start AND :end')
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d'))
            ->orderBy('t.dateExecution', 'ASC')
            ->addOrderBy('t.heureDebut', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
