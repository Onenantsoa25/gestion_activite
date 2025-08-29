<?php

// src/Controller/Login/LoginController.php
namespace App\Controller\Login;

use App\Entity\Utilisateur;
use App\Service\Authentification\TokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function page_login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si déjà authentifié, rediriger selon le rôle
        if ($this->getUser()) {
            return $this->redirectBasedOnRole();
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        Request $request,
        TokenManager $tokenManager,
        SessionInterface $session
    ): Response {
        // Si déjà authentifié, rediriger selon le rôle
        if ($this->getUser()) {
            return $this->redirectBasedOnRole();
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Si erreur d'authentification
        if ($error) {
            $this->addFlash('error', 'Identifiants invalides');
            return $this->redirectToRoute('app_accueil');
        }

        // Authentification réussie
        $user = $this->getUser();
        if ($user instanceof Utilisateur) {
            if (!$session->isStarted()) {
                $session->start();
            }
            $session->set('utilisateur', $user);
            $session->save();
            $token = $tokenManager->createToken($user);
            $response = $this->redirectBasedOnRole();
            $response->headers->setCookie(new Cookie(
                'AUTH_TOKEN',
                $token->getValue(),
                $token->getExpiresAt(),
                '/',
                null,
                false,
                true
            ));
            return $response;
        }

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): void
    {
        dump($session->get('utilisateur'));
        throw new \LogicException('This method will be intercepted by the logout key on your firewall.');
    }

    private function redirectBasedOnRole(): Response
    {
        $user = $this->getUser();
        
        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            return $this->redirectToRoute('manager_dashboard');
        }
        
        if (in_array('ROLE_COLLABORATEUR', $user->getRoles())) {
            return $this->redirectToRoute('collaborateur_dashboard');
        }

        $this->addFlash('error', 'Vous n\'avez pas les droits d\'accès');
        return $this->redirectToRoute('app_accueil');
    }
}