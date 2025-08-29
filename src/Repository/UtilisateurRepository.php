<?php
// src/Repository/UtilisateurRepository.php
namespace App\Repository;

use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Retourne tous les utilisateurs dont le rôle a l'ID = 2 (collaborateur)
     *
     * @return Utilisateur[]
     */
    // public function findAllByRole(?Role $role): array
    // {
    //     return $this->createQueryBuilder('u')
    //         ->join('u.role', 'r')
    //         ->where('r.id = :roleId')
    //         ->setParameter('roleId', $role->getId())
    //         ->getQuery()
    //         ->getResult();
    // }

    public function findAllByRole(?Role $role): array
    {
        $entityManager = $this->getEntityManager();

        $dql = "
            SELECT u, COALESCE(SUM(t.estimation), 0) as charge
            FROM App\Entity\Utilisateur u
            JOIN u.role r
            LEFT JOIN App\Entity\Tache t WITH t.utilisateur = u
            WHERE r.id = :roleId
            GROUP BY u
        ";

        $query = $entityManager->createQuery($dql);
        $query->setParameter('roleId', $role->getId());

        $result = $query->getResult();

        // Injecter la charge dans chaque utilisateur (en une seule boucle inévitable ici)
        foreach ($result as &$row) {
            if (is_array($row)) {
                /** @var Utilisateur $user */
                $user = $row[0];
                $charge = $row['charge'];
                $user->setCharges($charge);
                $row = $user; // on remplace le tableau par l'objet utilisateur modifié
            }
        }

        return $result;
    }


    public function findById(int $id): ?Utilisateur
    {
        return $this->find($id);
    }

    public function charge_travail(Utilisateur $utilisateur): float {
        $sql = "SELECT SUM(estimation) as charges FROM tache where id_utilisateur = :id_utilisateur";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
        ]);
        $result = $resultSet->fetchAssociative();

        // Si aucune tâche trouvée, SUM retourne null, on renvoie 0
        return $result['charges'] !== null ? (float)$result['charges'] : 0.0;
    }

}
