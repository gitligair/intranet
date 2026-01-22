<?php

namespace App\Controller;

use App\Services\WeatherLinkService;
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

    #Retourne sous forme de json les données d'une station donnée
    #[Route('/donnees/{station}', name: 'donnees_station')]
    public function station(ApimeteocentreService $client, string $stationKey, string $stationId): JsonResponse
    {
        try {
            $data = $client->getStationData($stationKey, $stationId);
            return $this->json($data);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Retourne les details d'une station donnée
    #[Route('/station', name: 'weather_stations')]
    public function stations(ApimeteocentreService $client, WeatherLinkService $weatherLinkService): JsonResponse
    {

        $apiKey = $_ENV['LIGAIR_API_KEY'];
        $apiSecret = $_ENV['LIGAIR_API_SECRET'];

        $stations = $weatherLinkService->getStations($apiKey, $apiSecret);

        dd($stations);

        try {
            $data = $client->getStations();
            return $this->json($data);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
