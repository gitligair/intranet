<?php

namespace App\Controller\Admin;

use App\Services\FormulaireAgileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/agile')]
class FormulaireAgileController extends AbstractController
{

    public function __construct(private FormulaireAgileService $formulaireAgileService)
    {
        $this->formulaireAgileService = $formulaireAgileService;
    }

    #[Route('/', name: 'formulaire_agile')]
    public function index(Request $request)
    {

        if ($request->isMethod('POST')) {
            $payload = $request->request->all();
            // IMPORTANT: CSRF
            if (!$this->isCsrfTokenValid('formulaire_agile_create', $payload['_token'] ?? '')) {
                throw $this->createAccessDeniedException('CSRF token invalide.');
            }

            // Ici tu récupères :
            // $payload['tachesParPole'][poleId] = [ [..], [..] ... ]
            // dump($payload['tachesParPole'] ?? []); die;

            $this->addFlash('success', 'Formulaire reçu (tâches par pôle).');
        }

        return $this->render('admin/formulaire_agile/index.html.twig', [
            'users' => $this->formulaireAgileService->getUsersLigair(),
            'poles' => $this->formulaireAgileService->getPoles(),
        ]);
    }

    // Fonction qui recupere les données du formulaire et les enregistre en base de données
    #[Route('/create', name: 'admin_formulaire_agile_create', methods: ['POST'])]
    public function create(Request $request)
    {
        $payload = $request->request->all();

        // IMPORTANT: CSRF
        if (!$this->isCsrfTokenValid('formulaire_agile_create', $payload['_token'] ?? '')) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        dd($_POST);

        // // Traitement des données du formulaire
        // $this->formulaireAgileService->handleFormulaireAgileData($payload);

        $this->addFlash('success', 'Formulaire reçu et traité avec succès.');

        return $this->redirectToRoute('formulaire_agile');
    }
}
