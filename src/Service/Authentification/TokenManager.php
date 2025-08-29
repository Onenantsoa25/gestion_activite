<?php
// src/Service/TokenManager.php
namespace App\Service\Authentification;

use App\Entity\AuthToken;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class TokenManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createToken(Utilisateur $user): AuthToken
    {
        $token = new AuthToken();
        $token->setUtilisateur($user);
        $token->setValue(bin2hex(random_bytes(32)));
        $token->setCreatedAt(new \DateTime());
        $token->setExpiresAt((new \DateTime())->modify('+1 day'));

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    public function validateToken(string $tokenValue): ?Utilisateur
    {
        $token = $this->em->getRepository(AuthToken::class)->findOneBy([
            'value' => $tokenValue,
            'expiresAt' => ['>' => new \DateTime()]
        ]);

        return $token ? $token->getUtilisateur() : null;
    }
}