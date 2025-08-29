<?php
// src/Repository/TypeActiviteRepository.php
namespace App\Repository;

use App\Entity\Activite;
use App\Entity\TypeActivite;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeActivite>
 */
class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    public function insertion_manager(?Activite $activite): void {
        if ($activite) {
            $this->getEntityManager()->persist($activite);
            $this->getEntityManager()->flush();
        }
    }

    public function findById(int $id): ?Activite
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllByUser(Utilisateur $utilisateur): array 
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT DISTINCT
                a.id_activite,
                a.activite AS nom_activite,
                a.date_debut,
                a.date_echeance,
                a.est_valide,
                ta.type_activite,
                ta.id_type_activite
            FROM 
                activite a
            JOIN 
                type_activite ta ON a.id_type_activite = ta.id_type_activite
            JOIN 
                tache t ON a.id_activite = t.id_activite
            WHERE 
                t.id_utilisateur = :id_utilisateur
            ORDER BY 
                a.date_echeance
        ";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId()
        ]);

        return $resultSet->fetchAllAssociative();
    }

    public function findAllNonTerminee(): array
    {
        $em = $this->getEntityManager();

        // $qb = $em->createQueryBuilder()
        //     ->select('a')
        //     ->from(Activite::class, 'a')
        //     ->where(
        //         $qb->expr()->notIn(
        //             'a.id',
        //             'SELECT at.idActivite FROM App\Entity\ActiviteTerminee at'
        //         )
        //     );

        // Comme on n'a pas d'entité ActiviteTerminee, on fait via SQL natif en transformant en objet
        $conn = $em->getConnection();
        $sql = "
            SELECT * 
            FROM activite a
        ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $rows = $resultSet->fetchAllAssociative();

        $activites = [];
        foreach ($rows as $row) {
            $activite = new Activite();
            $activite->setId((int)$row['id_activite']);
            $activite->setActivite($row['activite']);
            $activite->setDateDebut($row['date_debut'] ? new \DateTime($row['date_debut']) : null);
            $activite->setDateEcheance(new \DateTime($row['date_echeance']));
            $activite->setEstValide((bool)$row['est_valide']);
            // Tu peux aussi charger le typeActivite et utilisateurAuteur si nécessaire
            $activites[] = $activite;
        }

        return $activites;
    }

    // public function insert_collaborateur(Utilisateur $utilisateur, Activite $activite): void {
    //     $sql = 'INSERT INTO activite(activite, date_echeance, est_valider, id_type_activite, id_utilisateur_auteur';
    // }

    public function findAllNonValidees(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT a.*
            FROM activite a
            WHERE a.est_valide = 0
            AND a.id_activite NOT IN (
                SELECT asup.id_activite
                FROM activite_supprimees asup
            )
            ORDER BY a.date_echeance ASC
        ";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $rows = $resultSet->fetchAllAssociative();

        $activites = [];
        $em = $this->getEntityManager();
        $utilisateurRepo = $em->getRepository(Utilisateur::class);
        $typeRepo = $em->getRepository(TypeActivite::class);

        foreach ($rows as $row) {
            $activite = new Activite();
            $activite->setId((int)$row['id_activite']);
            $activite->setActivite($row['activite']);
            $activite->setDateDebut($row['date_debut'] ? new \DateTime($row['date_debut']) : null);
            $activite->setDateEcheance(new \DateTime($row['date_echeance']));
            $activite->setEstValide((bool)$row['est_valide']);

            // Charger l'utilisateur auteur si présent
            if (!empty($row['id_utilisateur_auteur'])) {
                $utilisateur = $utilisateurRepo->find((int) $row['id_utilisateur_auteur']);
                $activite->setUtilisateurAuteur($utilisateur);
            }
            $type = $typeRepo->find((int) $row['id_type_activite']);
            $activite->setTypeActivite($type);

            $activites[] = $activite;
        }

        return $activites;
    }

    public function validerActivite(int $idActivite): bool
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "UPDATE activite SET est_valide = 1 WHERE id_activite = :id";
        $stmt = $conn->prepare($sql);

        $rowCount = $stmt->executeStatement(['id' => $idActivite]);

        return $rowCount > 0; // true si au moins une ligne a été mise à jour
    }

    public function supprimerActivite(int $idActivite): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "INSERT INTO activite_supprimees (date_suppression, id_activite) 
                VALUES (:date_sup, :id)";

        $stmt = $conn->prepare($sql);

        $date = new \DateTime();

        $stmt->executeStatement([
            'date_sup' => $date->format('Y-m-d H:i:s'), // conversion en string SQL
            'id'       => $idActivite
        ]);
    }

    public function commencer(int $idActivite): void {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "update activite set date_debut=:date_debut where id_activite=:id";

        $stmt = $conn->prepare($sql);

        $date = new \DateTime();

        $stmt->executeStatement([
            'date_debut' => $date->format('Y-m-d H:i:s'), // conversion en string SQL
            'id'       => $idActivite
        ]);
    }

}