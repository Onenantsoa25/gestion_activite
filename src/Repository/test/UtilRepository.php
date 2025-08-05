<?php
namespace App\Repository\test;

use App\Entity\Util;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Util::class);
    }

    // public function findAll(): array {
    //     $connection = $this->getEntityManager()->getConnection();
    //     $query = "select u.id as id, coalesce ( (select h.name from historique_util h where h.id_util = u.id order by h.id), u.name ) as name from util u";

    //     $stmt = $connection->prepare($query);
    //     $result = $stmt->executeQuery();
    //     $data = $result->fetchAllAssociative();
    //     $utilList = [];
    //     foreach ($data as $row) {
    //         $util = new Util($row['id'], $row['name']);
    //         $utilList[] = $util;
    //     }
    //     return $utilList;
    // }

    public function findAll(): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT 
            u.id AS id,
            COALESCE(
                (
                    SELECT h.name 
                    FROM historique_util h 
                    WHERE h.id_util = u.id 
                    ORDER BY h.id DESC 
                    LIMIT 1
                ), 
                u.name
            ) AS name
        FROM util u
        ";
        $result = $conn->executeQuery($sql)->fetchAllAssociative();

        return array_map(fn($row) => new Util((int) $row['id'], $row['name']), $result);
    }

    public function insert(Util $util): void {
        $this->getEntityManager()->persist($util);
        $this->getEntityManager()->flush();
    }

    // Ajoute ici tes méthodes personnalisées si besoin
}
