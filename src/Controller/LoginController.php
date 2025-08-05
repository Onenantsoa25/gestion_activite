<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController {
    
    #[Route('/', name: 'app_accueil')]
    public function page_login(): Response {
        return $this->render('/manager/liste-collaborateur.html.twig');
    }

}