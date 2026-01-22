<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\PolesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FormulaireAgileService
{
    private $em;
    private $userPasswordHasher;
    private $users;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $users,
        private PolesRepository $polesRepository
    ) {
        $this->em = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->users = $users;
        $this->polesRepository = $polesRepository;
    }

    // Fonction qui liste le personnel Ligair
    public function getUsersLigair()
    {
        return $this->users->findAll();
    }

    // Recuperer les poles
    public function getPoles()
    {
        return $this->polesRepository->findAll();
    }
}
