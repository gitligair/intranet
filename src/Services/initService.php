<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class initService
{

    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function persist($entity)
    {
        return $this->em->persist($entity);
    }

    public function flush()
    {
        return $this->em->flush();
    }
    public function remove($entity)
    {
        return $this->em->remove($entity);
    }
}