<?php

namespace App\Services;

use Doctrine\DBAL\Connection;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeteoFranceService
{
    private HttpClientInterface $client;
    private string $token;
    private string $user;
    private string $baseUrl;
    private int $expiresAt = 0; // timestamp

    public function __construct(
        HttpClientInterface $client,
        string $meteoFranceToken,
        string $meteoFranceUser,
        string $meteoFranceBaseUrl,
        private Connection $connection
    ) {
        $this->client = $client;
        $this->token = $meteoFranceToken;
        $this->user = $meteoFranceUser;
        $this->baseUrl = $meteoFranceBaseUrl;
        $this->connection = $connection;
    }



    public function getApi(string $endpoint, array $query = []): array
    {
        $response = $this->client->request('GET', $this->baseUrl . $endpoint, [
            'headers' => [
                'apikey' => $this->token,
                'Accept' => 'application/json',
            ],
            'query' => $query,
        ]);



        return $response->toArray();
    }

    // Fonction qui transforme le CSV en tableau associatif
    public function csvToArray($csvContent)
    {
        $lines = explode("\n", $csvContent);
        $header = str_getcsv(array_shift($lines), ';');

        $data = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = str_getcsv($line, ';');
            $data[] = array_combine($header, $row);
        }

        return $data;
    }

    // Fonction qui transform un json en tableau associatif
    public function jsonToArray($jsonContent)
    {
        return json_decode($jsonContent, true);
    }


    public function listeStationsCsv()
    {
        $token = $this->token; // ou autre source

        $response = $this->client->request('GET', 'https://public-api.meteofrance.fr/public/DPObs/liste-stations', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'text/csv', // ou */*
            ],
        ]);

        $csvContent = $response->getContent();
        return $csvContent;
    }

    public function getDonneesStation($stationId, $date)
    {
        $token = $this->token; // ou autre source

        $response = $this->client->request('GET', 'https://public-api.meteofrance.fr/public/DPObs/station/horaire', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => '*/*', // ou */*
            ],
            'query' => [
                'id_station' => $stationId,
                'date' => $date,
                'format' => 'json',
            ],
        ]);

        $csvContent = $response->getContent();
        return $csvContent;
    }

    public function getObservations(): array
    {
        $stations = ['07240']; // WMO
        $params = ['t', 'rr', 'ff'];

        $response = $this->client->request(
            'GET',
            'https://public-api.meteofrance.fr/public/DPObs/observations/stations',
            [
                'auth_basic' => [
                    $_ENV['METEO_FRANCE_USER'],
                    $_ENV['METEO_FRANCE_TOKEN'],
                ],
                'query' => [
                    'stationIds' => implode(',', $stations),
                    'dateStart' => '2025-01-01T00:00:00Z',
                    'dateEnd' => '2025-01-07T23:59:59Z',
                    'parameters' => implode(',', $params),
                    'format' => 'json',
                ],
            ]
        );

        return $response->toArray();
    }

    // Fonction qui recupere les station_id si is_used = true
    public function getUsedStations(): array
    {
        $sql = "SELECT station_id FROM meteo_france_api.stations WHERE is_used = TRUE";
        $result = $this->connection->fetchAllAssociative($sql);
        return array_column($result, 'station_id');
    }

    // Fonction qui recupere les données 24h pour certaines stations
    public function fetchAllData($token): array
    {

        $departements = [45, 28, 41, 36, 37, 18]; // Exemple de départements : Loiret (45), Eure-et-Loir (28), Loir-et-Cher (41)
        $allStations = [];
        foreach ($departements as $dept) {
            $stations = $this->get_data_observations_by_departement($dept, $token);
            $data = $this->csvToArray($stations);
            $allStations = array_merge($allStations, $data);
        }

        // filtrer les donnees pour ne garder que les stations qui sont is_used = true
        $usedStationIds = $this->getUsedStations();
        $allStations = array_filter($allStations, function ($station) use ($usedStationIds) {
            return in_array($station['geo_id_insee'], $usedStationIds);
        });
        return $allStations;
    }

    // Fonction qui permet d'inserer les données dans la base de données postgres
    public function insertStations(array $stations): void
    {
        foreach ($stations as $station) {
            $stationId = intval($station['Id_station']);
            $idOmm = is_numeric($station['Id_omm']) ? intval($station['Id_omm']) : null;
            $nomUsuel = $station['Nom_usuel'];
            $latitude = is_numeric($station['Latitude']) ? floatval($station['Latitude']) : null;
            $longitude = is_numeric($station['Longitude']) ? floatval($station['Longitude']) : null;
            $altitude = is_numeric($station['Altitude']) ? intval($station['Altitude']) : null;
            $dateOuverture = !empty($station['Date_ouverture']) ? $station['Date_ouverture'] : null;
            $pack = $station['Pack'] ?? null;

            // On peut laisser departement et region null pour l'instant
            $sql = "
                INSERT INTO meteo_france_api.stations 
                (station_id, id_omm, nom_usuel, latitude, longitude, altitude, date_ouverture, pack, created_at, updated_at)
                VALUES 
                (:station_id, :id_omm, :nom_usuel, :latitude, :longitude, :altitude, :date_ouverture, :pack, NOW(), NOW())
                ON CONFLICT (station_id) DO UPDATE SET
                    id_omm = EXCLUDED.id_omm,
                    nom_usuel = EXCLUDED.nom_usuel,
                    latitude = EXCLUDED.latitude,
                    longitude = EXCLUDED.longitude,
                    altitude = EXCLUDED.altitude,
                    date_ouverture = EXCLUDED.date_ouverture,
                    pack = EXCLUDED.pack,
                    updated_at = NOW()
            ";

            $this->connection->executeStatement($sql, [
                'station_id' => $stationId,
                'id_omm' => $idOmm,
                'nom_usuel' => $nomUsuel,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'altitude' => $altitude,
                'date_ouverture' => $dateOuverture,
                'pack' => $pack,
            ]);
        }
    }

    // Fonction qui insere les données en base de données
    public function insertOrUpdateDonnees(array $donnees): void
    {
        $parametres = $this->connection->fetchAllKeyValue(
            "SELECT code, id FROM meteo_france_api.parametres"
        );

        $this->connection->beginTransaction();

        try {
            foreach ($donnees as $row) {

                if (!isset($row['geo_id_insee'])) {
                    continue;
                }

                $stationId = (int) $row['geo_id_insee'];

                $referenceTime = new \DateTimeImmutable($row['reference_time']);
                $insertTime    = new \DateTimeImmutable($row['insert_time']);
                $validityTime  = new \DateTimeImmutable($row['validity_time']);

                foreach ($parametres as $code => $parametreId) {

                    if (
                        !isset($row[$code]) ||
                        $row[$code] === '' ||
                        !is_numeric($row[$code])
                    ) {
                        continue;
                    }

                    $this->connection->executeStatement(
                        "
                    INSERT INTO meteo_france_api.donnees
                    (station_id, parametre_id, valeur,
                     reference_time, insert_time, validity_time,
                     created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ON CONFLICT (station_id, parametre_id, insert_time)
                    DO UPDATE SET
                        valeur = EXCLUDED.valeur,
                        reference_time = EXCLUDED.reference_time,
                        validity_time = EXCLUDED.validity_time,
                        updated_at = NOW()
                    ",
                        [
                            $stationId,
                            $parametreId,
                            (float) $row[$code],
                            $referenceTime->format('Y-m-d H:i:s'),
                            $insertTime->format('Y-m-d H:i:s'),
                            $validityTime->format('Y-m-d H:i:s'),
                        ]
                    );
                }

                unset($row);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }

        gc_collect_cycles();
    }



    // Fonction qui recupere les donnees d'observation de 24h pour les station d'un departement donné
    public function get_data_observations_by_departement($departement_code, $token)
    {
        // $token = $this->token; // ou autre source

        $response = $this->client->request('GET', 'https://public-api.meteofrance.fr/public/DPPaquetObs/v1/paquet/horaire?id-departement=' . $departement_code . '&format=csv', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'text/csv', // ou */*
            ],
        ]);

        $csvContent = $response->getContent();
        return $csvContent;
    }
}
