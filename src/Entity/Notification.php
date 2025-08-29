<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notification')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_notification', type: 'integer')]
    private ?int $idNotification = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Tache::class)]
    #[ORM\JoinColumn(name: 'id_tache', referencedColumnName: 'id_tache', nullable: true)]
    private ?Tache $tache = null;

    #[ORM\Column(name: 'type_notif', type: 'string', length: 100)]
    private string $typeNotif;

    #[ORM\Column(name: 'message', type: 'text')]
    private string $message;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(name: 'est_lue', type: 'boolean', options: ['default' => false])]
    private bool $estLue = false;

    // --- Getters et Setters ---

    public function getIdNotification(): ?int
    {
        return $this->idNotification;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getTache(): ?Tache
    {
        return $this->tache;
    }

    public function setTache(?Tache $tache): self
    {
        $this->tache = $tache;
        return $this;
    }

    public function getTypeNotif(): string
    {
        return $this->typeNotif;
    }

    public function setTypeNotif(string $typeNotif): self
    {
        $this->typeNotif = $typeNotif;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function isEstLue(): bool
    {
        return $this->estLue;
    }

    public function setEstLue(bool $estLue): self
    {
        $this->estLue = $estLue;
        return $this;
    }
}
