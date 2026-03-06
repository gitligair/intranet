<?php

namespace App\Controller\Admin;

use App\Entity\FormPartageinfos;
use App\Entity\FormPointscles;
use App\Entity\FormTachesprioritaires;
use App\Entity\Formulaire;
use App\Services\FormulaireAgileService;
use App\Services\initService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/agile')]
class FormulaireAgileController extends AbstractController
{

    public function __construct(private FormulaireAgileService $formulaireAgileService, private initService $initService, private AdminUrlGenerator $adminUrlGenerator,)
    {
        $this->formulaireAgileService = $formulaireAgileService;
        $this->initService = $initService;
    }

    #[Route('/', name: 'formulaire_agile')]
    public function index(Request $request)
    {



        $poles = $this->formulaireAgileService->getPoles();
        $responsablesIds = array_values(array_unique(array_filter(array_map(
            fn($p) => $p->getResponsable()?->getId(),
            $poles
        ))));

        return $this->render('admin/formulaire_agile/index.html.twig', [
            'users' => $this->formulaireAgileService->getUsersLigair(),
            'poles' => $this->formulaireAgileService->getPoles(),
            'responsablesIds' => $responsablesIds,
        ]);
    }

    // Fonction qui recupere les données du formulaire et les enregistre en base de données
    // #[Route('/create', name: 'admin_formulaire_agile_create', methods: ['POST'])]
    // public function create(Request $request)
    // {
    //     $payload = $request->request->all();

    //     // IMPORTANT: CSRF
    //     if (!$this->isCsrfTokenValid('formulaire_agile_create', $payload['_token'] ?? '')) {
    //         throw $this->createAccessDeniedException('CSRF token invalide.');
    //     }

    //     dd($_POST);

    //     // // Traitement des données du formulaire
    //     // $this->formulaireAgileService->handleFormulaireAgileData($payload);

    //     $this->addFlash('success', 'Formulaire reçu et traité avec succès.');

    //     return $this->redirectToRoute('formulaire_agile');
    // }

    #[Route('/create', name: 'admin_formulaire_agile_create', methods: ['POST'])]
    public function create_formulaire(
        Request $request

    ): Response {
        $payload = $request->request->all();

        if (!$this->isCsrfTokenValid('formulaire_agile_create', $payload['_token'] ?? '')) {
            throw $this->createAccessDeniedException('CSRF invalide');
        }

        $formulaire = new Formulaire();

        // date du jour
        $dateJour = $payload['date_jour'] ?? null; // "YYYY-MM-DD"
        $formulaire->setDateJour($dateJour ? new \DateTime($dateJour) : new \DateTime());
        // animateur
        $animateurId = $payload['animateurId'] ?? null;
        if ($animateurId) {
            $animateur = $this->formulaireAgileService->getUserById($animateurId);
            $formulaire->setAnimateur($animateur);
        } else {
            $formulaire->setAnimateur($this->getUser()); // fallback
        }

        // réunion tenue
        $formulaire->setReunionTenue(isset($payload['reunionTenue']));

        // présents[]
        foreach (($payload['presents'] ?? []) as $uid) {
            if ($u = $this->formulaireAgileService->getUserById($uid)) {
                $formulaire->getPresents()->add($u);
            }
        }

        // prochaine réunion
        if (!empty($payload['prochaineReunionAt'])) {
            $formulaire->setProchaineReunionAt(new \DateTimeImmutable($payload['prochaineReunionAt']));
        }

        // prochain animateur
        if (!empty($payload['prochainAnimateurId'])) {
            $formulaire->setProchainAnimateur($this->formulaireAgileService->getUserById($payload['prochainAnimateurId']));
        }

        $this->initService->persist($formulaire);
        $this->initService->flush(); // IMPORTANT : génère l'ID du formulaire

        // ===== Points clés par pôle =====
        $pointsParPole = $payload['pointsClesParPole'] ?? [];
        foreach ($pointsParPole as $poleId => $rows) {
            $pole = $this->formulaireAgileService->getPoleById($poleId);
            if (!$pole) continue;

            foreach ($rows as $row) {
                $texte = trim($row['texte'] ?? '');
                if ($texte === '') continue;

                $pc = new FormPointscles();
                $pc->setFormulaire($formulaire);
                $pc->setPole($pole);
                $pc->setTexte($texte);

                $this->initService->persist($pc);
            }
        }

        // ===== Partage info par pôle =====
        $partageParPole = $payload['partageInfoParPole'] ?? [];
        foreach ($partageParPole as $poleId => $rows) {
            $pole = $this->formulaireAgileService->getPoleById($poleId);
            if (!$pole) continue;

            foreach ($rows as $row) {
                $texte = trim($row['texte'] ?? '');
                if ($texte === '') continue;

                $pi = new FormPartageinfos();
                $pi->setFormulaire($formulaire);
                $pi->setPole($pole);
                $pi->setTexte($texte);
                $pi->setImportant(isset($row['important']));
                $pi->setARelancer(isset($row['aRelancer']));

                $this->initService->persist($pi);
            }
        }

        // ===== Tâches prioritaires générales =====
        $taches = $payload['tachesprioritaire'] ?? []; // adapte au name exact dans ton partial
        foreach ($taches as $t) {
            $desc = trim($t['description'] ?? '');
            if ($desc === '') continue;

            $task = new FormTachesprioritaires();
            $task->setFormulaire($formulaire);
            $task->setDescription($desc);

            if (!empty($t['pilote'])) $task->setPilote($this->formulaireAgileService->getUserById($t['pilote']));
            if (!empty($t['doublon'])) $task->setDoublon($this->formulaireAgileService->getUserById($t['doublon']));
            if (!empty($t['delai'])) $task->setDelai(new \DateTime($t['delai']));

            $this->initService->persist($task);
        }


        $this->initService->flush();

        $this->addFlash('success', 'Formulaire enregistré.');

        $url = $this->adminUrlGenerator
            ->setRoute('formulaire_agile')   // ton GET
            ->generateUrl();

        return $this->redirect($url);
    }
}
