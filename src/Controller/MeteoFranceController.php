<?php

namespace App\Controller;

use App\Services\MeteoFranceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MeteoFranceController extends AbstractController
{

    public function __construct(private MeteoFranceService $meteoFranceService, private HttpClientInterface $httpClient)
    {
        $this->meteoFranceService = $meteoFranceService;
        $this->httpClient = $httpClient;
    }

    #[Route('/meteo', name: 'app_meteo_france')]
    public function index(): Response
    {
        return $this->render('meteo_france/index.html.twig', [
            'controller_name' => 'MeteoFranceController',
        ]);
    }

    #[Route('/meteo/france', name: 'meteo_france')]
    public function meteo(): JsonResponse
    {
        $lesstations = ['45055001', '45004001'];
        $rows = [];
        foreach ($lesstations as $station) {
            $data = $this->meteoFranceService->getDonneesStation($station, '2025-01-01T12:00:00Z');
            $rows[$station] = json_decode($data, true);
        }
        dd($rows);
        return $this->json($rows);
    }

    #[Route('/meteo/stations-csv', name: 'meteo_stations_csv')]
    public function listeStationsCsv(): JsonResponse
    {
        $stations = $this->meteoFranceService->listeStationsCsv();
        $data = $this->meteoFranceService->csvToArray($stations);

        dd($data);
        $this->meteoFranceService->insertStations($data);

        return $this->json(array_values($data));
    }

    // permettant de recuperer les donnees pour les stations des departements definis dans l'array
    #[Route('/meteo/stations-departements', name: 'meteo_stations_departements')]
    public function listeStationsDepartements(): JsonResponse
    {

        $departements = [45, 28, 41, 36, 37, 18]; // Exemple de d��partements : Loiret (45), Eure-et-Loir (28), Loir-et-Cher (41)
        $allStations = [];
        foreach ($departements as $dept) {
            $stations = $this->meteoFranceService->get_data_observations_by_departement($dept, $token = null);
            $data = $this->meteoFranceService->csvToArray($stations);
            $allStations = array_merge($allStations, $data);
        }

        // filtrer les donnees pour ne garder que les stations qui sont is_used = true
        $usedStationIds = $this->meteoFranceService->getUsedStations();
        $allStations = array_filter($allStations, function ($station) use ($usedStationIds) {
            return in_array($station['geo_id_insee'], $usedStationIds);
        });
        foreach (array_chunk($allStations, 100) as $chunk) {
            $this->meteoFranceService->insertOrUpdateDonnees($chunk);
        }

        dd($allStations);

        return $this->json(array_values($allStations));
    }
}
