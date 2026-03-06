<?php

namespace App\Services;


use App\Repository\PolesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FormulaireAgileService
{

    private $users;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $users,
        private PolesRepository $polesRepository
    ) {

        $this->users = $users;
        $this->polesRepository = $polesRepository;
    }

    // Fonction qui liste le personnel Ligair
    public function getUsersLigair()
    {
        return $this->users->findAll();
    }

    // Fonction qui retourne un user par son id
    public function getUserById($id)
    {
        return $this->users->find($id);
    }

    // Recuperer les poles
    public function getPoles()
    {
        return $this->polesRepository->findAll();
    }

    // Fonction qui retourne un pole par son id
    public function getPoleById($id)
    {
        return $this->polesRepository->find($id);
    }
}
