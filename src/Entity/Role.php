<?php
// src/Entity/Role.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'role')]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private $role;

    // Getter pour id
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    // Getter pour role
    public function getRole(): ?string
    {
        return $this->role;
    }

    // Setter pour role
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }
}
