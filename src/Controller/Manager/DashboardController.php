<?php
// src/Controller/Manager/DashboardController.php
namespace App\Controller\Manager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DashboardController extends AbstractController
{
    #[Route('/manager/dashboard', name: 'manager_dashboard')]
    public function index(SessionInterface $session): Response
    {
        if($session->get('activite') !== null){
            $session->remove('activite');
        }
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        return $this->render('manager/dashboard.html.twig');
    }
}