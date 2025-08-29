<?php
namespace App\Service;

use App\Repository\TypeActiviteRepository;
use App\Entity\TypeActivite;

class TypeActiviteService
{
    private TypeActiviteRepository $typeActiviteRepository;

    public function __construct(TypeActiviteRepository $typeActiviteRepository) {
        $this->typeActiviteRepository = $typeActiviteRepository;
    }

    public function findAll(): array {
        return $this->typeActiviteRepository->findAll();
    }

    public function findById(int $id): ?TypeActivite {
        return $this->typeActiviteRepository->findById($id);
    }

}