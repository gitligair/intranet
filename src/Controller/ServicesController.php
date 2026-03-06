<?php

namespace App\Controller;


use App\Services\ApimeteocentreService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ServicesController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {
        return $this->render('services/index.html.twig', [
            'controller_name' => 'ServicesController',
        ]);
    }


    // Retourne les details des stations qu'on m'a partagé
    #[Route('/station', name: 'weather_stations')]
    public function stations(ApimeteocentreService $client): JsonResponse
    {
        $data = $client->listStations();

        try {
            return $this->json($data);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Retourne les données d'une station donnée
    #[Route('/station/{id}', name: 'weather_station_data')]
    public function stationData(ApimeteocentreService $client, string $id): JsonResponse
    {
        try {
            $data = $client->getStationData($id);
            return $this->json($data);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
