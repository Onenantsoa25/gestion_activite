<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tache_terminee')]
class TacheTerminee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_tache_terminee', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'date_terminee', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTerminee = null;

    #[ORM\Column(name: 'temps_passe', type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $tempsPasse = null;

    #[ORM\OneToOne(targetEntity: Tache::class, inversedBy: 'tacheTerminee')]
    #[ORM\JoinColumn(name: 'id_tache', referencedColumnName: 'id_tache', nullable: false, unique: true)]
    private ?Tache $tache = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(name: 'est_validee', type: Types::BOOLEAN, nullable: true)]
    private ?bool $estValidee = null;

    // Justificatif : fichier stocké en BLOB
    // #[ORM\Column(name: 'justificatif', type: Types::BLOB, nullable: true)]
    #[ORM\Column(name: 'justificatif', type: Types::STRING, length: 255, nullable: true)]
    private $justificatif = null; // ⚠ pas typé car Doctrine renvoie un resource (stream)

    // private $justifiee;

    // ---------------- Getters & Setters ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTerminee(): ?\DateTimeInterface
    {
        return $this->dateTerminee;
    }

    public function setDateTerminee(\DateTimeInterface $dateTerminee): self
    {
        $this->dateTerminee = $dateTerminee;
        return $this;
    }

    public function getTempsPasse(): ?string
    {
        return $this->tempsPasse;
    }

    public function setTempsPasse(string $tempsPasse): self
    {
        $this->tempsPasse = $tempsPasse;
        return $this;
    }

    public function getTache(): ?Tache
    {
        return $this->tache;
    }

    public function setTache(Tache $tache): self
    {
        $this->tache = $tache;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function isEstValidee(): ?bool
    {
        return $this->estValidee;
    }

    public function setEstValidee(?bool $estValidee): self
    {
        $this->estValidee = $estValidee;
        return $this;
    }

    /**
     * Retourne le fichier brut (BLOB)
     */
    public function getJustificatif(): ?string
    {
        // if ($this->justificatif === null) {
        //     return null;
        // }
        // Doctrine retourne un resource (stream), donc on le convertit en string
        return $this->justificatif;
    }

    /**
     * Définit le fichier (contenu binaire)
     */
    public function setJustificatif($justificatif): self
    {
        $this->justificatif = $justificatif;
        return $this;
    }
    public function __construct(?\DateTimeInterface $dateTerminee = null, ?string $tempsPasse = null, ?Tache $tache = null, ?string $commentaire = null, $justificatif = null, ?bool $estValidee = null) {
        $this->dateTerminee = $dateTerminee ?? new \DateTimeImmutable(); // par défaut = maintenant
        $this->tempsPasse = $tempsPasse;
        $this->tache = $tache;
        $this->commentaire = $commentaire;
        $this->estValidee = $estValidee ?? false; // par défaut = non validée
        $this->justificatif = $justificatif;
    }

    public static function totalDuree(array $tachesTerminees): float
    {
        return array_reduce($tachesTerminees, function ($carry, TacheTerminee $tache) {
            return $carry + (float) $tache->getTempsPasse();
        }, 0.0);
    }

}
