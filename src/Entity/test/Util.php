<?php
 namespace App\Entity\test;

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
 class Util {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    // public function __construct(int $id, string $nom) {
    //     $this->setId($id);
    //     $this->setName($nom);
    // }

    public function __construct(?int $id = null, ?string $nom = null) {
        if ($id !== null) {
            $this->setId($id);
        }
        if ($nom !== null) {
            $this->setName($nom);
        }
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }
    
    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }


 }