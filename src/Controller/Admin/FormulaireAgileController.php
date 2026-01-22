<?php

namespace App\Controller\Admin;

use App\Services\FormulaireAgileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/agile')]
class FormulaireAgileController extends AbstractController
{

    public function __construct(private FormulaireAgileService $formulaireAgileService)
    {
        $this->formulaireAgileService = $formulaireAgileService;
    }

    #[Route('/', name: 'forumlaire_agile')]
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
}
