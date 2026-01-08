<?php
// src/Service/NotificationService.php
namespace App\Services;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function notifyUser(
        User $recipient,
        string $title,
        ?string $body = null,
        ?string $link = null,
        array $meta = [],

    ): Notification {
        $n = (new Notification())
            ->setUserAssigne($recipient)
            ->setTitre($title)
            ->setBody($body)
            ->setLien($link)
            ->setMetadonnees($meta);

        $this->em->persist($n);
        $this->em->flush();

        return $n;
    }
}
