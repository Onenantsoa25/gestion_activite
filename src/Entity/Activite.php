<?php
// src/Entity/Activite.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activite')]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_activite', type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $activite;

    #[ORM\Column(name: 'date_debut', type: 'date', nullable: true)]
    private $dateDebut;

    #[ORM\Column(name: 'date_echeance', type: 'date')]
    private $dateEcheance;

    #[ORM\Column(name: 'est_valide', type: 'boolean')]
    private $estValide;

    #[ORM\ManyToOne(targetEntity: TypeActivite::class)]
    #[ORM\JoinColumn(name: 'id_type_activite', referencedColumnName: 'id_type_activite')]
    private $typeActivite;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur_auteur', referencedColumnName: 'id_utilisateur')]
    private $utilisateurAuteur;

    #[ORM\OneToMany(mappedBy: 'activite', targetEntity: Tache::class)]
    private $taches;

    private $estCommencee;

    // Getters & Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    // ... (autres getters/setters)

    public function getActivite(): ?string
    {
        return $this->activite;
    }

    public function setActivite(string $activite): void
    {
        $this->activite = $activite;
        // return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateEcheance(): \DateTimeInterface
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(\DateTimeInterface $dateEcheance): self
    {
        $this->dateEcheance = $dateEcheance;
        return $this;
    }

    public function isEstValide(): bool
    {
        return $this->estValide;
    }

    public function setEstValide(bool $estValide): self
    {
        $this->estValide = $estValide;
        return $this;
    }

    public function getTypeActivite(): ?TypeActivite
    {
        return $this->typeActivite;
    }

    public function setTypeActivite(?TypeActivite $typeActivite): self
    {
        $this->typeActivite = $typeActivite;
        return $this;
    }

    public function getUtilisateurAuteur(): ?Utilisateur
    {
        return $this->utilisateurAuteur;
    }

    public function setUtilisateurAuteur(?Utilisateur $utilisateurAuteur): self
    {
        $this->utilisateurAuteur = $utilisateurAuteur;
        return $this;
    }

    /**
     * @return Collection|Tache[]
     */
    public function getTaches()
    {
        return $this->taches;
    }

    public function addTache(Tache $tache): self
    {
        if (!$this->taches->contains($tache)) {
            $this->taches[] = $tache;
            $tache->setActivite($this);
        }
        return $this;
    }

    public function removeTache(Tache $tache): self
    {
        if ($this->taches->removeElement($tache)) {
            if ($tache->getActivite() === $this) {
                $tache->setActivite(null);
            }
        }
        return $this;
    }

    public function estCommencee(): bool {
        return $this->estCommencee;
    }

    public function setCommencee(bool $commencee): void {
        $this->estCommencee = $commencee;
    }

}