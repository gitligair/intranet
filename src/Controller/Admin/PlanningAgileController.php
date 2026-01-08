<?php

namespace App\Controller\Admin;


use App\Entity\User;
use App\Entity\Poles;
use App\Entity\Tache;
use App\Repository\TacheRepository;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/planning')]
class PlanningAgileController extends AbstractController
{
    #[Route('/', name: 'admin_planning')]
    public function index(EntityManagerInterface $em): Response
    {
        $poles = $em->getRepository(Poles::class)->findAll();

        $startOfWeek = (new \DateTime())->modify('monday this week');

        $endOfWeek = (clone $startOfWeek)->modify('+6 days');

        $taches = $em->getRepository(Tache::class)
            ->createQueryBuilder('t')
            ->where('t.dateExecution BETWEEN :start AND :end')
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->orderBy('t.dateExecution', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/planning_agile.html.twig', [
            'poles' => $poles,
            'taches' => $taches,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }

    #[Route('/grille_semaine', name: 'admin_planning_grille')]
    public function grille_semaine(EntityManagerInterface $em): Response
    {
        $poles = $em->getRepository(Poles::class)->findAll();

        $users = $em->getRepository(User::class)->findAll();

        $startOfWeek = (new \DateTime())->modify('monday this week');

        $endOfWeek = (clone $startOfWeek)->modify('+6 days');

        $taches = $em->getRepository(Tache::class)
            ->createQueryBuilder('t')
            ->where('t.dateExecution BETWEEN :start AND :end')
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->orderBy('t.dateExecution', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/grille_hebdomadaire.html.twig', [
            'poles' => $poles,
            'taches' => $taches,
            'users' => $users,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }

    #[Route('/week', name: 'admin_planning_week', methods: ['GET'])]
    public function loadWeek(Request $request, EntityManagerInterface $em): Response
    {
        $weekOffset = (int)$request->query->get('week_offset', 0);

        // Calcul ISO intelligent (ne change PAS l'URL)
        $today = new \DateTime();
        $startOfWeek = new \DateTime();
        $startOfWeek->setISODate(
            (int)$today->format('o'),
            (int)$today->format('W') + $weekOffset,
            1
        )->setTime(0, 0);

        $endOfWeek = (clone $startOfWeek)->modify('+6 days');

        $taches = $em->getRepository(Tache::class)
            ->createQueryBuilder('t')
            ->where('t.dateExecution BETWEEN :start AND :end')
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->orderBy('t.dateExecution', 'ASC')
            ->addOrderBy('t.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();

        $poles = $em->getRepository(Poles::class)->findAll();

        return $this->render('admin/_planning_content.html.twig', [
            'startOfWeek' => $startOfWeek,
            'endOfWeek'   => $endOfWeek,
            'taches'      => $taches,
            'poles'       => $poles,
            'weekOffset'  => $weekOffset,
        ]);
    }


    #[Route('/admin/planning/partial', name: 'admin_planning_partial')]
    public function partial(EntityManagerInterface $em): Response
    {
        $poles = $em->getRepository(Poles::class)->findAll();
        $startOfWeek = (new \DateTime())->modify('monday this week');
        $endOfWeek = (clone $startOfWeek)->modify('+6 days');

        $taches = $em->getRepository(Tache::class)
            ->createQueryBuilder('t')
            ->where('t.dateExecution BETWEEN :start AND :end')
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->orderBy('t.dateExecution', 'ASC')
            ->getQuery()
            ->getResult();

        // on renvoie seulement la partie HTML du planning
        return $this->render('admin/_planning_content.html.twig', [
            'poles' => $poles,
            'taches' => $taches,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }


    #[Route('/save', name: 'admin_planning_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em, NotificationService $notifier, AdminUrlGenerator $adminUrlGenerator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        try {
            // 📌 Pôle
            $pole = $em->getRepository(Poles::class)->find($data['pole_id']);
            // 🔒 Vérifier si l'utilisateur est responsable de CE pôle
            // if ($pole->getResponsable() !== $this->getUser()) {
            //     return new JsonResponse([
            //         'message' => 'Vous n\'etes pas responsable de ce pole pour ajouter une tache',
            //     ], 403);
            // }
            $tache = new Tache();
            $tache->setCreatedBy($this->getUser());
            $tache->setTitre($data['titre'] ?? 'Sans titre');
            $tache->setImportance($data['importance'] ?? 'moyenne');

            //description de la tache
            $tache->setDescription($data['description'] ?? 'Aucune description');

            // 🗓 Date d’exécution
            $tache->setDateExecution(new \DateTime($data['date']));

            // 🗓 Deadline
            $tache->setDeadline(
                !empty($data['deadline']) ? new \DateTime($data['deadline']) : null
            );

            // 🕒 Heures
            $tache->setHeureDebut(
                !empty($data['heureDebut']) ? new \DateTime($data['heureDebut']) : null
            );

            $tache->setHeureFin(
                !empty($data['heureFin']) ? new \DateTime($data['heureFin']) : null
            );

            if (!$pole) {
                return new JsonResponse(['error' => 'Pôle introuvable'], 400);
            }
            $tache->setPole($pole);

            // 👥 Assignés (ManyToMany)
            if (!empty($data['assignes'])) {
                foreach ($data['assignes'] as $userId) {
                    $user = $em->getRepository(User::class)->find($userId);
                    if ($user) {
                        $tache->addAssignerA($user);
                    }
                }
            }

            $em->persist($tache);
            $em->flush();


            $url = $adminUrlGenerator
                ->setController('App\Controller\Admin\TacheCrudController')
                ->setAction('detail')
                ->setEntityId($tache->getId())
                ->generateUrl();
            // ... après avoir persisté/flush la Tache $tache
            foreach ($tache->getAssignerA() as $user) {
                $notifier->notifyUser(
                    $user,
                    'Nouvelle tâche : ' . $tache->getTitre(),
                    sprintf(
                        '%s — %s → %s',
                        $tache->getDateExecution()?->format('Y-m-d'),
                        $tache->getHeureDebut()?->format('H:i'),
                        $tache->getHeureFin()?->format('H:i'),
                        $tache->getCreatedBy()
                    ),
                    $url,
                    ['taskId' => $tache->getId(), 'importance' => $tache->getImportance()]
                );
            }

            return new JsonResponse([
                'message' => 'Tâche enregistrée',
                'tache' => [
                    'id' => $tache->getId(),
                    'titre' => $tache->getTitre(),
                    'importance' => $tache->getImportance(),
                    'date' => $tache->getDateExecution()->format('Y-m-d'),
                    'assignes' => array_map(
                        fn($u) => $u->getId(),
                        $tache->getAssignerA()->toArray()
                    )
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }


    #[Route('/admin/planning/update/{id}', name: 'admin_planning_update', methods: ['POST', 'PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tache = $em->getRepository(Tache::class)->find($id);

        if (!$tache) {
            return new JsonResponse(['error' => 'Tâche introuvable'], 404);
        }

        if ($tache->getCreatedBy() !== $this->getUser()) {
            return new JsonResponse([
                'message' => 'Vous ne pouvez modifier que vos propres tâches crées',
            ], 403);
        }

        try {
            $tache->setTitre($data['titre']);
            $tache->setCreatedBy($this->getUser());
            $tache->setImportance($data['importance']);
            $tache->setDateExecution(new \DateTime($data['date']));
            $tache->setDeadline(!empty($data['deadline']) ? new \DateTime($data['deadline']) : null);

            $tache->setHeureDebut(!empty($data['heureDebut']) ? new \DateTime($data['heureDebut']) : null);
            $tache->setHeureFin(!empty($data['heureFin']) ? new \DateTime($data['heureFin']) : null);

            //description de la tache
            $tache->setDescription($data['description'] ?? 'Aucune description');
            // Pôle
            $pole = $em->getRepository(Poles::class)->find($data['pole_id']);
            if ($pole) $tache->setPole($pole);

            // Assignés
            $tache->getAssignerA()->clear();
            if (!empty($data['assignes'])) {
                foreach ($data['assignes'] as $uid) {
                    $user = $em->getRepository(User::class)->find($uid);
                    if ($user) $tache->addAssignerA($user);
                }
            }

            $em->flush();

            return new JsonResponse(['message' => 'Tâche mise à jour avec succès']);
        } catch (\Exception $e) {

            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/admin/planning/delete/{id}', name: 'admin_planning_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $tache = $em->getRepository(Tache::class)->find($id);

        if (!$tache) {
            return new JsonResponse(['error' => 'Tâche introuvable'], 404);
        }

        if ($tache->getCreatedBy() !== $this->getUser()) {
            return new JsonResponse([
                'message' => 'La tâche ne peut etre supprimée que par son createur',
            ], 403);
        }

        $em->remove($tache);
        $em->flush();

        return new JsonResponse(['message' => 'Tâche supprimée']);
    }

    // fonction qui recupere les users d'un pole donnée
    #[Route('/admin/planning/users-by-pole', name: 'admin_planning_users_by_pole', methods: ['POST'])]
    public function getUsersByPole(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $poleId = $data['pole_id'] ?? null;

        if (!$poleId) {
            return new JsonResponse(['error' => 'Pole manquant'], 400);
        }

        $pole = $em->getRepository(Poles::class)->find($poleId);

        if (!$pole) {
            return new JsonResponse(['error' => 'Pole introuvable'], 404);
        }

        $users = [];

        foreach ($pole->getPersonnel() as $user) {
            $users[] = [
                'id' => $user->getId(),
                'name' => $user->getPrenom() . ' ' . $user->getNom(),
            ];
        }

        return new JsonResponse(['users' => $users]);
    }

    // Verification des conflits horaire user
    #[Route('/admin/planning/check-dispo', name: 'admin_planning_check_dispo', methods: ['POST'])]
    public function checkDisponibilites(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $date = new \DateTime($data['date']);
        $assignes = $data['assignes'] ?? [];
        $heureDebut = new \DateTime($data['heureDebut']);
        $heureFin = new \DateTime($data['heureFin']);

        $conflicts = [];

        foreach ($assignes as $userId) {
            $qb = $em->createQueryBuilder()
                ->select('t')
                ->from(Tache::class, 't')
                ->join('t.assignerA', 'u')
                ->where('u.id = :uid')
                ->andWhere('t.dateExecution = :date')
                ->andWhere('(t.heureDebut < :heureFin AND t.heureFin > :heureDebut)')
                ->setParameter('uid', $userId)
                ->setParameter('date', $date)
                ->setParameter('heureDebut', $heureDebut)
                ->setParameter('heureFin', $heureFin);

            $result = $qb->getQuery()->getResult();

            if ($result) {
                $user = $em->getRepository(User::class)->find($userId);
                $conflicts[] = [
                    'user_id' => $userId,
                    'user' => $user->getPrenom() . " " . $user->getNom(),
                ];
            }
        }

        return new JsonResponse(['conflicts' => $conflicts]);
    }

    /***************************************************************
     ******************* PAGE RECAPITULATIF************************ 
     **************************************************************/
    #[Route('/recap', name: 'admin_planning_recap', methods: ['GET', 'POST'])]
    public function recap(EntityManagerInterface $em, Request $request): Response
    {
        $startOfWeek = new \DateTime('monday this week');
        $endOfWeek = new \DateTime('sunday this week');

        // 🔎 Récupération des filtres (POST en priorité, sinon GET)
        $source = $request->isMethod('POST') ? $request->request : $request->query;
        $filterPole = $source->get('pole');
        $filterUser = $source->get('user');

        $pole_concerne = new Poles();

        // 🔍 Query dynamique
        $qb = $em->getRepository(Tache::class)
            ->createQueryBuilder('t')
            ->where('t.dateExecution BETWEEN :start AND :end')
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->orderBy('t.dateExecution', 'ASC')
            ->addOrderBy('t.heureDebut', 'ASC');

        // 🎯 Filtre par pôle
        if (!empty($filterPole)) {
            $pole_concerne = $em->getRepository(Poles::class)->findOneById((int)$filterPole);
            $qb->andWhere('t.pole = :pid')
                ->setParameter('pid', (int)$filterPole);
        }

        // 👤 Filtre par utilisateur
        if (!empty($filterUser)) {
            $qb->join('t.assignerA', 'u')
                ->andWhere('u.id = :uid')
                ->setParameter('uid', (int)$filterUser);

            $user_concerne = new User();
            $user_concerne = $em->getRepository(User::class)->findOneById((int)$filterUser);
        }

        $taches = $qb->getQuery()->getResult();

        // Pour alimenter les selects
        $poles = $em->getRepository(Poles::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/planning_recap.html.twig', [
            'taches' => $taches,
            'poles' => $poles,
            'users' => $users,
            'pole_concerne' => $filterPole ? $pole_concerne : false,
            'user_concerne' => $filterUser ? $user_concerne : false,
            'filterPole' => (int)$filterPole,
            'filterUser' => (int)$filterUser,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }

    #[Route('/planning/recap/poles', name: 'admin_planning_recap_poles', methods: ['GET', 'POST'])]
    public function recapPoles(EntityManagerInterface $em, Request $request): Response
    {
        // Récupération FILTERS
        $poleFilter = $request->request->get('pole');
        $weekOffset = (int)($request->request->get('week_offset') ?? 0);

        // Calcul dynamique des semaines
        $start = new \DateTime("monday this week $weekOffset week");
        $end   = new \DateTime("sunday this week $weekOffset week");

        // Tâches filtrées
        $qb = $em->getRepository(Tache::class)->createQueryBuilder('t')
            ->where('t.dateExecution BETWEEN :s AND :e')
            ->setParameter('s', $start)
            ->setParameter('e', $end)
            ->orderBy('t.dateExecution', 'ASC');

        if (!empty($poleFilter)) {
            $qb->andWhere('t.pole = :pid')->setParameter('pid', $poleFilter);
        }

        $taches = $qb->getQuery()->getResult();

        // Listes
        $poles = $em->getRepository(Poles::class)->findAll();

        return $this->render('admin/recap_pole.html.twig', [
            'taches' => $taches,
            'poles' => $poles,
            'poleFilter' => $poleFilter,
            'weekOffset' => $weekOffset,
            'startOfWeek' => $start,
            'endOfWeek' => $end,
        ]);
    }


    #[Route('/planning/recap/users', name: 'admin_planning_recap_users', methods: ['GET', 'POST'])]
    public function recapUsers(EntityManagerInterface $em, Request $request): Response
    {
        $userFilter = $request->request->get('user');
        $weekOffset = (int)($request->request->get('week_offset') ?? 0);

        $start = new \DateTime("monday this week $weekOffset week");
        $end   = new \DateTime("sunday this week $weekOffset week");

        $qb = $em->getRepository(Tache::class)->createQueryBuilder('t')
            ->leftJoin('t.assignerA', 'u')
            ->addSelect('u')
            ->where('t.dateExecution BETWEEN :s AND :e')
            ->setParameter('s', $start)
            ->setParameter('e', $end)
            ->orderBy('t.dateExecution', 'ASC');

        if (!empty($userFilter)) {
            $qb->andWhere('u.id = :uid')->setParameter('uid', $userFilter);
        }

        $taches = $qb->getQuery()->getResult();
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/recap_user.html.twig', [
            'taches' => $taches,
            'users' => $users,
            'userFilter' => $userFilter,
            'weekOffset' => $weekOffset,
            'startOfWeek' => $start,
            'endOfWeek' => $end,
        ]);
    }

    #[Route('/get/{id}', name: 'admin_planning_get', methods: ['GET'])]
    public function getTache(Tache $tache): JsonResponse
    {
        return new JsonResponse([
            'id'         => $tache->getId(),
            'titre'      => $tache->getTitre(),
            'importance' => $tache->getImportance(),
            'deadline'   => $tache->getDeadline()?->format('Y-m-d'),
            'date'       => $tache->getDateExecution()?->format('Y-m-d'),
            'heureDebut' => $tache->getHeureDebut()?->format('H:i'),
            'heureFin'   => $tache->getHeureFin()?->format('H:i'),
            'pole'       => $tache->getPole()?->getId(),
        ]);
    }

    // Timeline PAR UTILISATEUR 
    #[Route('/timeline/week', name: 'admin_planning_timeline_week')]
    public function loadWeekTimeline(Request $request, TacheRepository $repo, EntityManagerInterface $em): Response
    {
        $offset = $request->query->getInt('week_offset', 0);
        $poles = $em->getRepository(Poles::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();

        $startOfWeek = (new \DateTime())->modify("monday this week")->modify("$offset week");
        $endOfWeek = (clone $startOfWeek)->modify("+4 days");

        $taches = $repo->findBetweenDates($startOfWeek, $endOfWeek);

        return $this->render('admin/_partials/_planning_user.html.twig', [
            'taches' => $taches,
            'poles' => $poles,
            'users' => $users,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'weekOffset' => $offset,
        ]);
    }
}
