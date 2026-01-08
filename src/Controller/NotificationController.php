<?php
// src/Controller/NotificationController.php
namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'api_notifications_list', methods: ['GET'])]
    public function list(NotificationRepository $repo): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        // ⚠️ Assure-toi que ton repository implémente bien ces méthodes avec userAssigne
        $items = $repo->findLatestForUser($user, 15);
        $unread = $repo->countUnreadForUser($user);

        return $this->json([
            'unread' => $unread,
            'items' => array_map(static fn(Notification $n) => [
                'id' => $n->getId(),
                'title' => $n->getTitre(),
                'body' => $n->getBody(),
                'link' => $n->getLien(),
                'createdAt' => $n->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'read' => $n->isRead(),
            ], $items),
        ]);
    }

    #[Route('/notifications/{id}/read', name: 'api_notifications_read', methods: ['POST'])]
    public function markRead(Notification $n, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        // Vérifie la propriété selon ton entité (ici: userAssigne)
        if ($n->getUserAssigne() !== $user) {
            return $this->json(['error' => 'forbidden'], 403);
        }

        if (!$n->isRead()) {
            $n->markRead();
            $em->flush();
        }

        return $this->json(['ok' => true]);
    }

    #[Route('/notifications/read-all', name: 'api_notifications_read_all', methods: ['POST'])]
    public function readAll(NotificationRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        // ⚠️ Important: utilise bien userAssigne ici
        $items = $repo->findBy(['userAssigne' => $user, 'readAt' => null]);

        foreach ($items as $n) {
            $n->markRead();
        }
        $em->flush();

        return $this->json(['ok' => true]);
    }
}
