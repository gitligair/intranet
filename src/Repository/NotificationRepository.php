<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Notification;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function countUnreadForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.userAssigne = :u')->setParameter('u', $user)
            ->andWhere('n.readAt IS NULL')
            ->getQuery()->getSingleScalarResult();
    }

    /** @return Notification[] */
    public function findLatestForUser(User $user, int $limit = 15): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.userAssigne = :u')->setParameter('u', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
}
