<?php

// src/Security/TokenAuthenticator.php
namespace App\Security;

use App\Entity\AuthToken;
use App\Service\Authentification\TokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    private TokenManager $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function supports(Request $request): ?bool
    {
        // Ne pas intercepter la route de déconnexion
        return $request->cookies->has('AUTH_TOKEN') 
            && $request->getPathInfo() !== '/logout';
    }

    public function authenticate(Request $request): Passport
    {
        $tokenValue = $request->cookies->get('AUTH_TOKEN');
        if (null === $tokenValue) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $user = $this->tokenManager->validateToken($tokenValue);
        if (null === $user) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Authentication Failed', Response::HTTP_UNAUTHORIZED);
    }
}