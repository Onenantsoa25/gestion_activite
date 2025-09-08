<?php
namespace App\Service;

use App\Repository\ActiviteRepository;
use App\Entity\Notification;
use App\Entity\Utilisateur;
use App\Repository\NotificationRepository;

class NotificationService
{
    private NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository) {
        $this->notificationRepository = $notificationRepository;
    }

    public function findByUtilisateur(Utilisateur $utilisateur): array {
        return $this->notificationRepository->findByUtilisateur($utilisateur);
    }


    public function compte_notif(Utilisateur $utilisateur): int {
        return $this->notificationRepository->countNonLuesByUtilisateur($utilisateur);
    }

    public function lire(int $id): void {
        $notification = $this->notificationRepository->find($id);
        $this->notificationRepository->marquerCommeLue($notification);
    }

}