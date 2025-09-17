<?php

namespace App\Entity;

use App\Repository\TypeAnomalieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'type_anomalie')]
class TypeAnomalie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_type_anomalie', type: Types::INTEGER)]
    private ?int $idTypeAnomalie = null;

    #[ORM\Column(name: 'type_anomalie', type: Types::STRING, length: 50)]
    private string $typeAnomalie;

    #[ORM\OneToMany(mappedBy: 'typeAnomalie', targetEntity: Anomalie::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $anomalies;

    // Constructor
    public function __construct()
    {
        $this->anomalies = new ArrayCollection();
    }

    // Getters and Setters
    public function getIdTypeAnomalie(): ?int
    {
        return $this->idTypeAnomalie;
    }

    public function getTypeAnomalie(): string
    {
        return $this->typeAnomalie;
    }

    public function setTypeAnomalie(string $typeAnomalie): self
    {
        $this->typeAnomalie = $typeAnomalie;
        return $this;
    }

    /**
     * @return Collection<int, Anomalie>
     */
    public function getAnomalies(): Collection
    {
        return $this->anomalies;
    }

    public function addAnomalie(Anomalie $anomalie): self
    {
        if (!$this->anomalies->contains($anomalie)) {
            $this->anomalies->add($anomalie);
            $anomalie->setTypeAnomalie($this);
        }
        return $this;
    }

    public function removeAnomalie(Anomalie $anomalie): self
    {
        if ($this->anomalies->removeElement($anomalie)) {
            // set the owning side to null (unless already changed)
            if ($anomalie->getTypeAnomalie() === $this) {
                $anomalie->setTypeAnomalie(null);
            }
        }
        return $this;
    }

    // String representation
    public function __toString(): string
    {
        return $this->typeAnomalie;
    }
}