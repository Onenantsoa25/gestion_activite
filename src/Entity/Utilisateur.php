<?php
// src/Entity/Utilisateur.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'utilisateur')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_utilisateur', type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', unique: true)]
    private $matricule;

    #[ORM\Column(name: 'mot_de_passe', type: 'string')]
    private $password;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: 'id_role', referencedColumnName: 'id')]
    private $role;

    private float $charges;

    // Implémentation de UserInterface

    public function getUsername(): string
    {
        return (string) $this->matricule;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role->getRole())];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return null; // inutile avec bcrypt ou sodium
    }

    public function eraseCredentials(): void
    {
        // Si des données sensibles sont stockées temporairement, les effacer ici
    }

    // ✅ Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatricule(): ?int
    {
        return $this->matricule;
    }

    public function setMatricule(int $matricule): self
    {
        $this->matricule = $matricule;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->matricule;
    }

    public function setCharges(?float $charges): void {
        $this->charges = $charges;
    }

    public function getCharges(): float {
        return $this->charges;
    }

}
