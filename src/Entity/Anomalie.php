<?php

namespace App\Entity;

use App\Repository\AnomalieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'anomalie')]
class Anomalie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_anomalie', type: Types::INTEGER)]
    private ?int $idAnomalie = null;

    #[ORM\Column(name: 'est_resolue', type: Types::BOOLEAN)]
    private bool $estResolue = false;

    #[ORM\Column(name: 'date_anomalie', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $dateAnomalie;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', nullable: false)]
    private ?Utilisateur $utilisateur;

    // #[ORM\Column(name: 'matricule', type: Types::INTEGER)]
    // private int $matricule;

    #[ORM\ManyToOne(targetEntity: TypeAnomalie::class)]
    #[ORM\JoinColumn(name: 'id_type_anomalie', referencedColumnName: 'id_type_anomalie', nullable: false)]
    private TypeAnomalie $typeAnomalie;

    #[ORM\Column(name: 'message', type: Types::STRING, length: 255, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne(targetEntity: Tache::class)]
    #[ORM\JoinColumn(name: 'id_tache', referencedColumnName: 'id_tache', nullable: true)]
    private ?Tache $tache = null;

    // Constructor
    public function __construct()
    {
        $this->dateAnomalie = new \DateTime();
    }

    // Getters and Setters
    public function getIdAnomalie(): ?int
    {
        return $this->idAnomalie;
    }

    public function isEstResolue(): bool
    {
        return $this->estResolue;
    }

    public function setEstResolue(bool $estResolue): self
    {
        $this->estResolue = $estResolue;
        return $this;
    }

    public function getDateAnomalie(): \DateTimeInterface
    {
        return $this->dateAnomalie;
    }

    public function setDateAnomalie(\DateTimeInterface $dateAnomalie): self
    {
        $this->dateAnomalie = $dateAnomalie;
        return $this;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
        // return $this;
    }

    // public function getMatricule(): int
    // {
    //     return $this->matricule;
    // }

    // public function setMatricule(int $matricule): self
    // {
    //     $this->matricule = $matricule;
    //     return $this;
    // }

    public function getTypeAnomalie(): TypeAnomalie
    {
        return $this->typeAnomalie;
    }

    public function setTypeAnomalie(?TypeAnomalie $typeAnomalie): self
    {
        $this->typeAnomalie = $typeAnomalie;
        return $this;
    }

    // Helper methods
    public function resolve(): self
    {
        $this->estResolue = true;
        return $this;
    }

    public function unresolve(): self
    {
        $this->estResolue = false;
        return $this;
    }

    public function setMessage(?string $message): void {
        $this->message = $message;
    }

    public function getMessage(): string {
        return $this->message;
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

}