<?php
// src/Entity/Tache.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tache')]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_tache', type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private $tache;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $debut;

    #[ORM\Column(name: 'date_echeance', type: 'datetime', nullable: false)]
    private $dateEcheance;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur')]
    private $utilisateur;

    #[ORM\ManyToOne(targetEntity: Activite::class/*, inversedBy: 'taches'*/, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'id_activite', referencedColumnName: 'id_activite')]
    private $activite;

    #[ORM\Column(type: 'float', nullable: true)]
    private $estimation;

    private ?bool $terminee = false;

    private float $temps_passee = 0.0;

    #[ORM\OneToOne(mappedBy: 'tache', targetEntity: TacheTerminee::class, cascade: ['persist', 'remove'])]
    private ?TacheTerminee $tacheTerminee = null;

    public function getTacheTerminee(): ?TacheTerminee
    {
        return $this->tacheTerminee;
    }

    public function setTacheTerminee(?TacheTerminee $tacheTerminee): self
    {
        // évite les incohérences
        if ($tacheTerminee && $tacheTerminee->getTache() !== $this) {
            $tacheTerminee->setTache($this);
        }

        $this->tacheTerminee = $tacheTerminee;
        return $this;
    }

    // Getters & Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    // ... (autres getters/setters)

    public function getTache(): ?string
    {
        return $this->tache;
    }

    public function setTache(?string $tache): void
    {
        $this->tache = $tache;
        // return $this;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(?\DateTimeInterface $debut): void
    {
        $this->debut = $debut;
        // return $this;
    }

    public function getDateEcheance(): ?\DateTimeInterface
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(?\DateTimeInterface $dateEcheance): void
    {
        $this->dateEcheance = $dateEcheance;
        // return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
        // return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): void
    {
        $this->activite = $activite;
        // return $this;
    }

    public function getEstimation(): ?float
    {
        return $this->estimation;
    }

    public function setEstimation(?float $estimation): void
    {
        $this->estimation = $estimation;
    }

    public function isTerminee(): ?bool
    {
        return $this->terminee;
    }

    public function setTerminee(?bool $terminee): void
    {
        $this->terminee = $terminee;
        // return $this;
    }

    public function getTemps_passee(): float {
        return $this->temps_passee;
    }

    public function setTemps_passe(float $temps_passee): void {
        $this->temps_passee = $temps_passee;
    }

}