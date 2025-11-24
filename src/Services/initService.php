<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class initService
{

    private $em;
    private $userPasswordHasher;
    private $usersRepo;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, UserRepository $usersRepo)
    {
        $this->em = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->usersRepo = $usersRepo;
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

    // Fonction qui liste le personnel Ligair
    public function liste_ligair()
    {
        $liste = [
            [
                "prenom" => "Assane",
                "nom" => "MBOUP",
                "email" => "mboup@ligair.fr",
                "motdepass" => "mboup",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Corine",
                "nom" => "ROBIN",
                "email" => "robin@ligair.fr",
                "motdepass" => "robin",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Lobna",
                "nom" => "BEN RAIS",
                "email" => "ben_rais@ligair.fr",
                "motdepass" => "brn_rais",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Aly",
                "nom" => "NDIAYE",
                "email" => "ndiaye@ligair.fr",
                "motdepass" => "ndiaye",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Amélie",
                "nom" => "DE FILIPPIS",
                "email" => "de_filippis@ligair.fr",
                "motdepass" => "de_filippis",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Lise",
                "nom" => "GONNETAN",
                "email" => "gonnetan@ligair.fr",
                "motdepass" => "gonnetan",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Fréderic",
                "nom" => "LE FUR",
                "email" => "le_fur@ligair.fr",
                "motdepass" => "le_fur",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Walid",
                "nom" => "LAROUSSI",
                "email" => "laroussi@ligair.fr",
                "motdepass" => "laroussi",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Jérôme",
                "nom" => "RANGOGNIO",
                "email" => "rangognio@ligair.fr",
                "motdepass" => "rangognio",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Sara",
                "nom" => "ROBERT",
                "email" => "robert@ligair.fr",
                "motdepass" => "robert",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Patrice",
                "nom" => "COLIN",
                "email" => "colin@ligair.fr",
                "motdepass" => "colin",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Patricia",
                "nom" => "BOULAY DROUARD",
                "email" => "boulay_drouard@ligair.fr",
                "motdepass" => "boulay_drouard",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Carole",
                "nom" => "FLAMBARD",
                "email" => "flambard@ligair.fr",
                "motdepass" => "flambard",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Patrick",
                "nom" => "MERCIER",
                "email" => "mercier@ligair.fr",
                "motdepass" => "mercier",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Abderrazak",
                "nom" => "YAHYAOUI",
                "email" => "yahyaoui@ligair.fr",
                "motdepass" => "yahyaoui",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Florent",
                "nom" => "BORDIER",
                "email" => "bordier@ligair.fr",
                "motdepass" => "bordier",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Christophe",
                "nom" => "Chalumeau",
                "email" => "chalumeau@ligair.fr",
                "motdepass" => "chalumeau",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Margaux",
                "nom" => "BEAUREPERE",
                "email" => "beaurepere@ligair.fr",
                "motdepass" => "beaurepere",
                "role" => "ROLE_LIGAIR"
            ],
            [
                "prenom" => "Elvira",
                "nom" => "YANOVSKA",
                "email" => "yanovska@ligair.fr",
                "motdepass" => "yanovska",
                "role" => "ROLE_LIGAIR"
            ]
        ];
        return $liste;
    }


    // Fonction qui insere un user dans la base 
    public function insert_user(array $infosUsers)
    {


        // Supprime tous les users
        $this->usersRepo->truncate();

        foreach ($infosUsers as $unUser) {

            // Creer le nouvel utilisateur et inserer en base
            $user = new User();
            $user->setPrenom($unUser['prenom'])
                ->setNom($unUser['nom'])
                ->setEmail($unUser['email'])
                ->setPassword($this->userPasswordHasher->hashPassword($user, $unUser['motdepass']))
                ->setRoles([$unUser['role']])
                ->setIsOnline(true);

            $this->persist($user);
        }
        $this->flush();
    }
}
