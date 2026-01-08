<?php
// src/EventSubscriber/TaskAssignmentSubscriber.php
namespace App\EventSubscriber;

use App\Entity\Tache;
use App\Entity\User;
use App\Services\NotificationService;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class TaskAssignmentSubscriber
{
    public function __construct(private NotificationService $notifier) {}

    public function getSubscribedEvents(): array
    {
        return [Events::postPersist, Events::postUpdate];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->notifyAssignees($args->getObject(), true);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->notifyAssignees($args->getObject(), false);
    }

    private function notifyAssignees(object $entity, bool $isNew): void
    {
        if (!$entity instanceof Tache) return;

        /** @var User[] $assignees */
        $assignees = $entity->getAssignerA()->toArray(); // adapte

        $title = ($isNew ? 'Nouvelle tâche: ' : 'Tâche mise à jour: ') . $entity->getTitre();
        $body  = sprintf(
            "%s — %s → %s",
            $entity->getDateExecution()->format('Y-m-d'),
            $entity->getHeureDebut()?->format('H:i'),
            $entity->getHeureFin()?->format('H:i')
        );
        $link  = sprintf(
            '/admin?crudAction=detail&crudControllerFqcn=%s&entityId=%d',
            urlencode('App\\Controller\\Admin\\TaskCrudController'),
            $entity->getId()
        );

        foreach ($assignees as $u) {
            $this->notifier->notifyUser(
                $u,
                $title,
                $body,
                $link,
                ['taskId' => $entity->getId(), 'importance' => $entity->getImportance()]
            );
        }
    }
}
