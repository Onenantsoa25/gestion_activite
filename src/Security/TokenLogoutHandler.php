<?php

// src/Security/TokenLogoutHandler.php
namespace App\Security;

use App\Service\TokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenLogoutHandler implements LogoutHandlerInterface
{
    private TokenManager $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        $tokenValue = $request->cookies->get('AUTH_TOKEN');
        if ($tokenValue) {
            // Implémentez la suppression du token si nécessaire
            $response->headers->clearCookie('AUTH_TOKEN');
        }
    }
}