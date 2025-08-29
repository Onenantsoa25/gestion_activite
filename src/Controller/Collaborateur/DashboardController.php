<?php
// src/Controller/Collaborateur/DashboardController.php
namespace App\Controller\Collaborateur;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class DashboardController extends AbstractController
{
    #[Route('/collaborateur/dashboard', name: 'collaborateur_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        return $this->render('/collaborateur/dashboard.html.twig');
    }
}