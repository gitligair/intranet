<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LigairController extends AbstractController
{
    #[Route('/', name: 'app_ligair')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin');
    }

    // Fonction qui affiche les differents onglets de l'application
    #[Route('/onglets', name: 'app_ligair_onglets')]
    public function onglets(): Response
    {
        return $this->render('ligair/index.html.twig', [
            'controller_name' => 'LigairController',
            'onglets' => [
                'Accueil',
                'Clients',
                'Commandes',
                'Produits',
                'Statistiques',
                'Paramètres'
            ]
        ]);
    }
}