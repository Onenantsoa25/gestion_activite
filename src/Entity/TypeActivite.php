<?php
// src/Entity/TypeActivite.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'type_activite')]
class TypeActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_type_activite', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'type_activite', type: 'string', length: 50)]
    private $typeActivite;

    #[ORM\OneToMany(mappedBy: 'typeActivite', targetEntity: Activite::class)]
    private $activites;

    public function __construct()
    {
        $this->activites = new ArrayCollection();
    }

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeActivite(): ?string
    {
        return $this->typeActivite;
    }

    public function setTypeActivite(string $typeActivite): self
    {
        $this->typeActivite = $typeActivite;
        return $this;
    }

    /**
     * @return Collection|Activite[]
     */
    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activite $activite): self
    {
        if (!$this->activites->contains($activite)) {
            $this->activites[] = $activite;
            $activite->setTypeActivite($this);
        }
        return $this;
    }

    public function removeActivite(Activite $activite): self
    {
        if ($this->activites->removeElement($activite)) {
            // set the owning side to null (unless already changed)
            if ($activite->getTypeActivite() === $this) {
                $activite->setTypeActivite(null);
            }
        }
        return $this;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }
}