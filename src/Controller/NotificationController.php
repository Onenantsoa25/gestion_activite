<?php
// src/Controller/NotificationController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\NotificationService;
use App\Service\TacheService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class NotificationController extends AbstractController
{
    private NotificationService $notificationService;
    private TacheService $tacheService;

    public function __construct(NotificationService $notificationService, TacheService $tacheService)
    {
        $this->notificationService = $notificationService;
        $this->tacheService = $tacheService;
    }

    #[Route('/collaborateur/notifications', name: 'notifications', methods: ['GET'])]
    public function getNotifications(SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $user = $this->getUser();

        $notifications = $this->notificationService->findByUtilisateur($user);

        return $this->render('collaborateur/notification.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/api/notifications/count', name: 'notifications_count', methods: ['GET'])]
    public function getNotificationCount(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $user = $this->getUser();

        if (!$user) {
            return $this->json(['count' => 0]);
        }

        $count = $this->notificationService->compte_notif($user);

        return $this->json(['count' => $count]);
    }

    #[Route('/notification/planifier-tache/{id}', name: 'planifier_tache_notif', methods: ['GET'])]
    public function planifier(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        $tache = $this->tacheService->findById($id);
        return $this->render('collaborateur/planifier-tache.html.twig', ['tache' => $tache]);
    }
}
