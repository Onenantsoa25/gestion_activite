<?php
// src/Repository/TypeActiviteRepository.php
namespace App\Repository;

use App\Entity\TypeActivite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeActivite>
 */
class TypeActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeActivite::class);
    }

    /**
     * Récupère tous les types d'activité
     * @return TypeActivite[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.typeActivite', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un type d'activité par son ID
     * @param int $id
     * @return TypeActivite|null
     */
    public function findById(int $id): ?TypeActivite
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
}