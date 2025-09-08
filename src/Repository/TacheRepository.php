<?php
// src/Repository/TypeActiviteRepository.php
namespace App\Repository;

use App\Entity\Tache;
use App\Entity\Activite;
use App\Entity\TypeActivite;
use App\Entity\Utilisateur;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeActivite>
 */
class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    public function insertion_manager(?Tache $tache): void
    {
        if ($tache) {
            $this->getEntityManager()->persist($tache);
            $this->getEntityManager()->flush();
        }
    }

    // public function findAllByActivite(?Activite $activite): array
    // {
    //     if (!$activite) {
    //         return [];
    //     }

    //     $conn = $this->getEntityManager()->getConnection();

    //     // $sql = "SELECT * FROM tache WHERE id_activite = :id_activite";
    //     $sql = "SELECT 
    //                 t.id_tache,
    //                 t.tache,
    //                 COALESCE(h.debut, t.debut) AS debut,
    //                 COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
    //                 t.id_utilisateur,
    //                 t.id_activite,
    //                 COALESCE(h.estimation, t.estimation) AS estimation
    //             FROM tache t
    //             LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
    //             WHERE t.id_activite = :id_activite";
    //     $stmt = $conn->prepare($sql);
    //     $resultSet = $stmt->executeQuery([
    //         'id_activite' => $activite->getId(),
    //     ]);

    //     $results = $resultSet->fetchAllAssociative();

    //     // Convert raw data to Tache entities if needed
    //     return $this->getEntityManager()->getRepository(Tache::class)->findBy([
    //         'activite' => $activite,
    //     ]);
    // }

    // public function findAllByActivite(?Activite $activite): array
    // {
    //     if (!$activite) {
    //         return [];
    //     }

    //     $conn = $this->getEntityManager()->getConnection();

    //     $sql = "SELECT 
    //                 t.id_tache,
    //                 t.tache,
    //                 COALESCE(h.debut, t.debut) AS debut,
    //                 COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
    //                 t.id_utilisateur,
    //                 t.id_activite,
    //                 COALESCE(h.estimation, t.estimation) AS estimation
    //             FROM tache t
    //             LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
    //             WHERE t.id_activite = :id_activite
    //             AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

    //     $stmt = $conn->prepare($sql);
    //     $resultSet = $stmt->executeQuery([
    //         'id_activite' => $activite->getId(),
    //     ]);

    //     $rows = $resultSet->fetchAllAssociative();
    //     $em = $this->getEntityManager(); // récupérer EntityManager une seule fois
    //     $utilisateurRepo = $em->getRepository(Utilisateur::class);
    //     $taches = [];
    //     foreach ($rows as $row) {
    //         $tache = new Tache();
    //         $tache->setId($row['id_tache']); // ⚠️ si tu n’as pas de setter pour l’ID, il faudra faire autrement
    //         $tache->setTache($row['tache']);
    //         $tache->setDebut(new \DateTime($row['debut']));
    //         $tache->setDateEcheance(new \DateTime($row['date_echeance']));
    //         $tache->setEstimation((float)$row['estimation']);
    //         // $utilisateurRepository = new UtilisateurRepository();
    //         if($row['id_utilisateur'] != null){
    //             $tache->setUtilisateur($utilisateurRepo->find($row['id_utilisateur']));
    //         }
    //         // si tu veux lier l'utilisateur et l'activité
    //         $tache->setActivite($activite);
    //         // $tache->setUtilisateur(... récupérer via repository ...)

    //         $taches[] = $tache;
    //     }

    //     return $taches;
    // }

    public function findAllByActivite(?Activite $activite): array
    {
        if (!$activite) {
            return [];
        }

        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT 
                    t.id_tache
                FROM tache t
                WHERE t.id_activite = :id_activite
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'id_activite' => $activite->getId(),
        ]);

        $rows = $resultSet->fetchAllAssociative();
        $em = $this->getEntityManager(); // récupérer EntityManager une seule fois
        $utilisateurRepo = $em->getRepository(Utilisateur::class);
        $taches = [];
        foreach ($rows as $row) {

            $taches[] = $this->findById($row['id_tache']);
        }

        return $taches;
    }


    // public function findById(int $id): ?Tache
    // {
    //     return $this->find($id);
    // }
    public function findById(int $id): ?Tache
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
                    t.id_utilisateur,
                    t.id_activite,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_tache = :id_tache
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        $row = $stmt->executeQuery([
            'id_tache' => $id,
        ])->fetchAssociative();

        if (!$row) {
            return null; // aucune tâche trouvée
        }

        // On récupère l’entité Tache "classique"
        $tache = $this->getEntityManager()->getRepository(Tache::class)->find($row['id_tache']);
        if ($tache) {
            // On met à jour ses propriétés avec les valeurs de l’historique
            $tache->setDebut(new \DateTime($row['debut']));
            $tache->setDateEcheance(new \DateTime($row['date_echeance']));
            $tache->setEstimation((float)$row['estimation']);
        }

        return $tache;
    }

    public function attribuer(?int $id_tache, ?int $id_utilisateur): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "UPDATE tache SET id_utilisateur = :id_utilisateur WHERE id_tache = :id_tache";
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement([
            'id_utilisateur' => $id_utilisateur,
            'id_tache' => $id_tache,
        ]);
    }

    public function findAllByUser(Utilisateur $utilisateur): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = "SELECT * FROM tache WHERE id_utilisateur = :id_utilisateur";
        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
                    t.id_utilisateur,
                    t.id_activite,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
        ]);

        $results = $resultSet->fetchAllAssociative();

        // Convert raw data to Tache entities if needed
        return $this->getEntityManager()->getRepository(Tache::class)->findBy([
            'utilisateur' => $utilisateur,
        ]);
    }

    // public function findCollab_activite(Utilisateur $utilisateur, Activite $activite): array {
    //     $sql = "SELECT * FROM tache WHERE id_utilisateur = :id_utulisateur AND id_activite = :id_activite";
    // }

    public function terminer(int $id_tache, DateTime $date, float $temps_passe): void
    {
        $sql = "INSERT INTO tache_terminee (id_tache, date_terminee, temps_passe) VALUES (:id_tache, :date_terminee, :temps_passe)";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement([
            'id_tache' => $id_tache,
            'date_terminee' => $date->format('Y-m-d H:i:s'),
            'temps_passe' => $temps_passe,
        ]);
    }

    public function est_terminee(int $id_tache): bool
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(*) FROM tache_terminee WHERE id_tache = :id_tache";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'id_tache' => $id_tache,
        ]);

        return (bool) $resultSet->fetchOne();
    }

    public function getTemps_passee(int $id_tache): float
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT temps_passe 
                FROM tache_terminee 
                WHERE id_tache = :id_tache";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'id_tache' => $id_tache,
        ]);

        $value = $resultSet->fetchOne();

        return $value !== false ? (float) $value : 0.0;
    }

    // public function planifier($id_tache, DateTime $debut)
    // {
    //     $conn = $this->getEntityManager()->getConnection();

    //     $sql = "UPDATE tache SET date_debut = :date_debut WHERE id_tache = :id_tache";
    //     $stmt = $conn->prepare($sql);
    //     $stmt->executeStatement([
    //         'date_debut' => $debut->format('Y-m-d H:i:s'),
    //         'id_tache' => $id_tache,
    //     ]);
    // }

    public function planifier(int $id_tache, \DateTime $debut): void
    {
        $em = $this->getEntityManager();
        $tache = $em->getRepository(Tache::class)->find($id_tache);

        if ($tache) {
            $tache->setDebut($debut);
            $em->persist($tache);
            $em->flush();
        }
    }

    // public function findTachesDuJourParUtilisateur(Utilisateur $utilisateur): array
    // {
    //     $em = $this->getEntityManager();

    //     $todayStart = (new \DateTime('today'))->setTime(0, 0, 0);
    //     $todayEnd = (new \DateTime('today'))->setTime(23, 59, 59);

    //     $qb = $em->createQueryBuilder();
    //     $qb->select('t')
    //         ->from(Tache::class, 't')
    //         ->andWhere('t.utilisateur = :utilisateur')
    //         ->andWhere('t.debut BETWEEN :start AND :end')
    //         ->setParameter('utilisateur', $utilisateur)
    //         ->setParameter('start', $todayStart)
    //         ->setParameter('end', $todayEnd);

    //     return $qb->getQuery()->getResult();
    // }


   public function findTachesDuJourParUtilisateur(Utilisateur $utilisateur): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $todayStart = (new \DateTime('today'))->format('Y-m-d 00:00:00');
        $todayEnd   = (new \DateTime('today'))->format('Y-m-d 23:59:59');

        // $sql = "
        //     SELECT *
        //     FROM tache t
        //     WHERE t.id_utilisateur = :id_utilisateur
        //     AND t.debut BETWEEN :start AND :end
        //     AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
        //     ORDER BY t.debut ASC
        // ";

        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
                    t.id_utilisateur,
                    t.id_activite,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND COALESCE(h.debut, t.debut) BETWEEN :start AND :end
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                ORDER BY COALESCE(h.debut, t.debut) ASC";

        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'start' => $todayStart,
            'end' => $todayEnd,
        ])->fetchAllAssociative();

        $taches = [];
        foreach ($results as $row) {
            $taches[] = $this->findById($row['id_tache']);
        }

        return $taches;
    } 

    // public function findTachesParUtilisateurEtDate(Utilisateur $utilisateur, \DateTimeInterface $date): array
    // {
    //     $em = $this->getEntityManager();

    //     // Si c'est déjà un DateTimeImmutable, on l'utilise directement
    //     if ($date instanceof \DateTimeImmutable) {
    //         $dateImmutable = $date;
    //     } else {
    //         // Convertir un DateTime mutable en DateTimeImmutable
    //         $dateImmutable = \DateTimeImmutable::createFromMutable($date);
    //     }

    //     $dayStart = $dateImmutable->setTime(0, 0, 0);
    //     $dayEnd = $dateImmutable->setTime(23, 59, 59);

    //     $qb = $em->createQueryBuilder();
    //     $qb->select('t')
    //         ->from(Tache::class, 't')
    //         ->andWhere('t.utilisateur = :utilisateur')
    //         ->andWhere('t.debut BETWEEN :start AND :end')
    //         ->setParameter('utilisateur', $utilisateur)
    //         ->setParameter('start', $dayStart)
    //         ->setParameter('end', $dayEnd)
    //         ->orderBy('t.debut', 'ASC');

    //     return $qb->getQuery()->getResult();
    // }

    public function findTachesParUtilisateurEtDate(Utilisateur $utilisateur, \DateTimeInterface $date): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $dayStart = $date->format('Y-m-d 00:00:00');
        $dayEnd   = $date->format('Y-m-d 23:59:59');

        // $sql = "
        //     SELECT * 
        //     FROM tache t
        //     WHERE t.id_utilisateur = :id_utilisateur
        //     AND t.debut BETWEEN :dayStart AND :dayEnd
        //     AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
        //     ORDER BY t.debut ASC
        // ";

        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
                    t.id_utilisateur,
                    t.id_activite,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND COALESCE(h.debut, t.debut) BETWEEN :dayStart AND :dayEnd
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                ORDER BY COALESCE(h.debut, t.debut) ASC";

        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'dayStart' => $dayStart,
            'dayEnd' => $dayEnd,
        ])->fetchAllAssociative();

        $taches = [];
        foreach ($results as $row) {
            $taches[] = $this->find($row['id_tache']);
        }

        return $taches;
    }

    public function findTachesEnCours(Utilisateur $utilisateur): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $today = new \DateTime('today');

        // Récupère toutes les taches non terminées commencées avant aujourd'hui
        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND COALESCE(h.debut, t.debut) < :todayStart
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'todayStart' => $today->format('Y-m-d 00:00:00'),
        ])->fetchAllAssociative();

        $taches = [];
        foreach ($results as $row) {
            $debut = new \DateTime($row['debut']);
            $estimation = (float)$row['estimation'];

            $fin = $this->calculerFinTravail($debut, $estimation);

            if ($fin >= $today) {
                $taches[] = $this->find($row['id_tache']);
            }
        }

        return $taches;
    }

/**
 * Calcule la date de fin en tenant compte des heures de travail
 */
    private function calculerFinTravail(\DateTime $debut, float $heures): \DateTime
    {
        $fin = clone $debut;
        $restant = $heures;

        while ($restant > 0) {
            // Si weekend, passer au lundi 7h30
            if (in_array((int)$fin->format('N'), [6, 7])) {
                $fin->modify('next monday 07:30');
                continue;
            }

            // Définir plages de travail du jour
            $matinStart = (clone $fin)->setTime(7, 30);
            $matinEnd   = (clone $fin)->setTime(12, 0);
            $apremStart = (clone $fin)->setTime(13, 0);
            $apremEnd   = (clone $fin)->setTime(16, 30);

            // Choisir où on est dans la journée
            if ($fin < $matinStart) {
                $fin = $matinStart;
            } elseif ($fin > $matinEnd && $fin < $apremStart) {
                $fin = $apremStart;
            } elseif ($fin >= $apremEnd) {
                $fin->modify('+1 day')->setTime(7, 30);
                continue;
            }

            // Combien d'heures dispo dans ce créneau ?
            if ($fin >= $matinStart && $fin < $matinEnd) {
                $dispo = ($matinEnd->getTimestamp() - $fin->getTimestamp()) / 3600;
            } else {
                $dispo = ($apremEnd->getTimestamp() - $fin->getTimestamp()) / 3600;
            }

            if ($restant <= $dispo) {
                $fin->modify("+{$restant} hour");
                $restant = 0;
            } else {
                $fin->modify("+{$dispo} hour");
                $restant -= $dispo;
            }
        }

        return $fin;
    }

    public function findTachesDuJour(Utilisateur $utilisateur): array
    {
        // Récupérer les deux listes
        $tachesAujourdhui = $this->findTachesDuJourParUtilisateur($utilisateur);
        $tachesEnCours    = $this->findTachesEnCours($utilisateur);

        // Fusionner les deux tableaux
        $toutes = array_merge($tachesAujourdhui, $tachesEnCours);

        // Supprimer les doublons éventuels (même id_tache)
        $toutes = array_unique($toutes, SORT_REGULAR);

        // Trier par date de début (COALESCE(h.debut, t.debut))
        usort($toutes, function ($a, $b) {
            $debutA = $a->getDebut(); // à adapter si tu utilises Historique
            $debutB = $b->getDebut();

            return $debutA <=> $debutB;
        });

        return $toutes;
    }

    public function getBusinessDaysBetween(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $dates = [];

        // Toujours utiliser DateTimeImmutable pour éviter les effets de bord
        $current = $startDate instanceof \DateTimeImmutable ? $startDate : \DateTimeImmutable::createFromMutable($startDate);
        $end = $endDate instanceof \DateTimeImmutable ? $endDate : \DateTimeImmutable::createFromMutable($endDate);

        while ($current <= $end) {
            $dayOfWeek = (int)$current->format('N');
            if ($dayOfWeek <= 5) {
                $dates[] = $current; // Immutable, pas besoin de clone
            }
            $current = $current->modify('+1 day'); // retourne un nouvel objet
        }

        return $dates;
    }


    public function findPlanningDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate, Utilisateur $utilisateur): array
    {
        $planning = [];
        $dates = $this->getBusinessDaysBetween($startDate, $endDate);

        foreach ($dates as $date) {
            $taches = $this->findTachesParUtilisateurEtDate($utilisateur, $date);
            $planning[] = [
                'date' => $date,
                'taches' => $taches,
                'nb_taches' => count($taches)
            ];
        }

        return $planning;
    }

    public function tachesNonTerminees(Utilisateur $utilisateur): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = "SELECT * FROM tache t WHERE t.id_utilisateur = :id_utilisateur AND t.id_tache NOT IN (
        //     SELECT id_tache FROM tache_terminee
        // ) ORDER BY t.debut ASC";

        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
                    t.id_utilisateur,
                    t.id_activite,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                ORDER BY COALESCE(h.debut, t.debut) ASC";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
        ]);

        $results = $resultSet->fetchAllAssociative();

        // Convertir en entités Tache si nécessaire
        $tacheRepository = $this->getEntityManager()->getRepository(Tache::class);
        $taches = [];
        foreach ($results as $row) {
            $taches[] = $tacheRepository->find($row['id_tache']);
        }

        return $taches;
    }

    public function tachesNonTermineesActivite(Utilisateur $utilisateur, TypeActivite $activite): array
    {
        $conn = $this->getEntityManager()->getConnection();

    //     $sql = "
    //     SELECT * 
    //     FROM tache t 
    //     WHERE t.id_activite = :id_activite
    //       AND t.id_utilisateur = :id_utilisateur
    //       AND t.id_tache NOT IN (
    //           SELECT id_tache FROM tache_terminee
    //       )
    //     ORDER BY t.debut ASC
    // ";

        // $sql = "SELECT 
        //             t.id_tache,
        //             t.tache,
        //             COALESCE(h.debut, t.debut) AS debut,
        //             COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
        //             t.id_utilisateur,
        //             t.id_activite,
        //             COALESCE(h.estimation, t.estimation) AS estimation
        //         FROM tache t
        //         LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
        //         WHERE t.id_activite = :id_activite
        //         AND t.id_utilisateur = :id_utilisateur
        //         AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
        //         ORDER BY COALESCE(h.debut, t.debut) ASC";

        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
                    t.id_utilisateur,
                    t.id_activite,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                JOIN activite a on t.id_activite = a.id_activite
                WHERE a.id_type_activite = :id_activite 
                AND t.id_utilisateur = :id_utilisateur
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                ORDER BY COALESCE(h.debut, t.debut) ASC";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'id_activite' => $activite->getId(),
        ]);

        $results = $resultSet->fetchAllAssociative();

        // Convertir en entités Tache
        $tacheRepository = $this->getEntityManager()->getRepository(Tache::class);
        $taches = [];
        foreach ($results as $row) {
            $taches[] = $tacheRepository->find($row['id_tache']);
        }

        return $taches;
    }

    public function tempsPasseAujourdHui(Utilisateur $utilisateur): float
    {
        $conn = $this->getEntityManager()->getConnection();

        $todayStart = (new \DateTime('today'))->format('Y-m-d 00:00:00');
        $todayEnd   = (new \DateTime('today'))->format('Y-m-d 23:59:59');

        $sql = "SELECT COALESCE(SUM(tt.temps_passe), 0) AS total_temps
                FROM tache_terminee tt
                INNER JOIN tache t ON t.id_tache = tt.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                    AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                    AND tt.date_terminee BETWEEN :start AND :end";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'start' => $todayStart,
            'end' => $todayEnd,
        ])->fetchAssociative();

        return (float) $result['total_temps'];
    }

    public function tempsPasseSemaine(Utilisateur $utilisateur): float
    {
        $conn = $this->getEntityManager()->getConnection();

        // Début de semaine (lundi 00:00:00)
        $startOfWeek = (new \DateTimeImmutable('monday this week'))->setTime(0, 0, 0);
        // Fin de semaine (dimanche 23:59:59)
        $endOfWeek = (new \DateTimeImmutable('sunday this week'))->setTime(23, 59, 59);

        $sql = "SELECT COALESCE(SUM(tt.temps_passe), 0) AS total_temps
                FROM tache_terminee tt
                INNER JOIN tache t ON t.id_tache = tt.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                    AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                    AND tt.date_terminee BETWEEN :start AND :end";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'start' => $startOfWeek->format('Y-m-d H:i:s'),
            'end' => $endOfWeek->format('Y-m-d H:i:s'),
        ])->fetchAssociative();

        return (float) $result['total_temps'];
    }

    public function countTachesEnCours(Utilisateur $utilisateur): int
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = "
        //     SELECT COUNT(*) 
        //     FROM tache t
        //     WHERE t.id_utilisateur = :id_utilisateur
        //     AND t.debut <= NOW()
        //     AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
        // ";

        $sql = "SELECT COUNT(*) 
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND COALESCE(h.debut, t.debut) <= NOW()
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        return (int) $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
        ])->fetchOne();
    }

    public function countTachesAFaire(Utilisateur $utilisateur): int
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = "
        //     SELECT COUNT(*) 
        //     FROM tache t
        //     WHERE t.id_utilisateur = :id_utilisateur
        //     AND (t.debut IS NULL OR t.debut > NOW())
        //     AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
        // ";

        $sql = "SELECT COUNT(*) 
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND (COALESCE(h.debut, t.debut) IS NULL OR COALESCE(h.debut, t.debut) > NOW())
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        return (int) $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
        ])->fetchOne();
    }

    public function countTachesTermineesAujourdHui(Utilisateur $utilisateur): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $todayStart = (new \DateTime('today'))->format('Y-m-d 00:00:00');
        $todayEnd   = (new \DateTime('today'))->format('Y-m-d 23:59:59');

        // $sql = "
        //     SELECT COUNT(*) 
        //     FROM tache_terminee tt
        //     INNER JOIN tache t ON t.id_tache = tt.id_tache
        //     WHERE t.id_utilisateur = :id_utilisateur
        //     AND tt.date_terminee BETWEEN :start AND :end
        // ";

        $sql = "SELECT COUNT(*) 
                FROM tache_terminee tt
                INNER JOIN tache t ON t.id_tache = tt.id_tache
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                    AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                    AND tt.date_terminee BETWEEN :start AND :end";

        $stmt = $conn->prepare($sql);
        return (int) $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'start' => $todayStart,
            'end' => $todayEnd,
        ])->fetchOne();
    }

    public function countTachesNonTermineesParDate(Utilisateur $utilisateur, \DateTimeInterface $date): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $dayStart = $date->format('Y-m-d 00:00:00');
        $dayEnd   = $date->format('Y-m-d 23:59:59');

        // $sql = " SELECT COUNT(*)  FROM tache t WHERE t.id_utilisateur = :id_utilisateur AND t.debut BETWEEN :dayStart AND :dayEnd AND t.id_tache NOT IN (
        //         SELECT id_tache 
        //         FROM tache_terminee )";
        $sql = "SELECT COUNT(*)  
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND COALESCE(h.debut, t.debut) BETWEEN :dayStart AND :dayEnd
                AND t.id_tache NOT IN (
                    SELECT id_tache 
                    FROM tache_terminee
                )
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        return (int) $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'dayStart' => $dayStart,
            'dayEnd' => $dayEnd,
        ])->fetchOne();
    }

    public function terminerTache(int $id_tache, \DateTimeInterface $dateTerminee, float $tempsPasse): bool
    {
        $em = $this->getEntityManager();
        $tache = $em->getRepository(Tache::class)->find($id_tache);

        if (!$tache) {
            return false; // tâche inexistante
        }

        // Vérifier si déjà terminée
        if ($this->est_terminee($id_tache)) {
            return false; // déjà terminée
        }

        // Insertion en base
        $conn = $em->getConnection();
        $sql = "INSERT INTO tache_terminee (id_tache, date_terminee, temps_passe) 
                VALUES (:id_tache, :date_terminee, :temps_passe)";
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement([
            'id_tache'      => $id_tache,
            'date_terminee' => $dateTerminee->format('Y-m-d H:i:s'),
            'temps_passe'   => $tempsPasse,
        ]);

        // Mettre à jour l’objet Tache en mémoire
        $tache->setTerminee(true);
        $tache->setTemps_passe($tempsPasse);
        $em->persist($tache);
        $em->flush();

        return true;
    }

    public function countTachesNonTermineesParActivite(Activite $activite): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(*) 
            FROM tache t
            WHERE t.id_activite = :id_activite
            AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
            AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        $count = $stmt->executeQuery([
            'id_activite' => $activite->getId(),
        ])->fetchOne();

        return (int) $count;
    }


    public function tempsPasseAujourdHui_equipe(): float
    {
        $conn = $this->getEntityManager()->getConnection();

        $todayStart = (new \DateTime('today'))->format('Y-m-d 00:00:00');
        $todayEnd   = (new \DateTime('today'))->format('Y-m-d 23:59:59');

        $sql = "SELECT COALESCE(SUM(tt.temps_passe), 0) AS total_temps
        FROM tache_terminee tt
        INNER JOIN tache t ON t.id_tache = tt.id_tache
        WHERE tt.date_terminee BETWEEN :start AND :end
        AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'start' => $todayStart,
            'end' => $todayEnd,
        ])->fetchAssociative();

        return (float) $result['total_temps'];
    }

    public function tempsPasseSemaine_equipe(): float
    {
        $conn = $this->getEntityManager()->getConnection();

        // Début de semaine (lundi 00:00:00)
        $startOfWeek = (new \DateTimeImmutable('monday this week'))->setTime(0, 0, 0);
        // Fin de semaine (dimanche 23:59:59)
        $endOfWeek = (new \DateTimeImmutable('sunday this week'))->setTime(23, 59, 59);

        $sql = "SELECT COALESCE(SUM(tt.temps_passe), 0) AS total_temps
                FROM tache_terminee tt
                INNER JOIN tache t ON t.id_tache = tt.id_tache
                WHERE tt.date_terminee BETWEEN :start AND :end
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'start' => $startOfWeek->format('Y-m-d H:i:s'),
            'end' => $endOfWeek->format('Y-m-d H:i:s'),
        ])->fetchAssociative();

        return (float) $result['total_temps'];
    }

    public function countTachesEnCours_equipe(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = "
        //     SELECT COUNT(*) 
        //     FROM tache t
        //     WHERE t.debut <= NOW()
        //     AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
        // ";

        $sql = "SELECT COUNT(*) 
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE COALESCE(h.debut, t.debut) <= NOW()
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        return (int) $stmt->executeQuery()->fetchOne();
    }

    public function countTachesAFaire_equipe(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = "
        //     SELECT COUNT(*) 
        //     FROM tache t
        //     WHERE (t.debut IS NULL OR t.debut > NOW())
        //     AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
        // ";

        $sql = "SELECT COUNT(*) 
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE (COALESCE(h.debut, t.debut) IS NULL OR COALESCE(h.debut, t.debut) > NOW())
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        return (int) $stmt->executeQuery()->fetchOne();
    }

    public function countTachesTermineesAujourdHui_equipe(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $todayStart = (new \DateTime('today'))->format('Y-m-d 00:00:00');
        $todayEnd   = (new \DateTime('today'))->format('Y-m-d 23:59:59');

        // $sql = "
        //     SELECT COUNT(*) 
        //     FROM tache_terminee tt
        //     INNER JOIN tache t ON t.id_tache = tt.id_tache
        //     WHERE tt.date_terminee BETWEEN :start AND :end
        // ";

        $sql = "SELECT COUNT(*) 
                FROM tache_terminee tt
                INNER JOIN tache t ON t.id_tache = tt.id_tache
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE tt.date_terminee BETWEEN :start AND :end
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";

        $stmt = $conn->prepare($sql);
        return (int) $stmt->executeQuery([
            'start' => $todayStart,
            'end' => $todayEnd,
        ])->fetchOne();
    }


    public function findTachesParTypeActivite(int $id_typeActivite): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = "
        //     SELECT t.* 
        //     FROM tache t
        //     INNER JOIN activite a ON t.id_activite = a.id_activite
        //     WHERE a.id_type_activite = :id_type_activite
        //     ORDER BY t.debut ASC
        // ";

        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
                    t.id_utilisateur,
                    t.id_activite,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                INNER JOIN activite a ON t.id_activite = a.id_activite
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE a.id_type_activite = :id_type_activite
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                ORDER BY COALESCE(h.debut, t.debut) ASC";

        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'id_type_activite' => $id_typeActivite,
        ])->fetchAllAssociative();

        $taches = [];
        foreach ($results as $row) {
            $taches[] = $this->find($row['id_tache']);
        }

        return $taches;
    }

    public function modifier(Tache $tache): void {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "INSERT INTO historique_tache(date_echeance, id_tache, estimation, debut) VALUES (:echeance, :id_tache, :estimation, :debut)";
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement([
            'echeance' => $tache->getDateEcheance()->format('Y-m-d H:i:s'),
            'id_tache' => $tache->getId(),
            'estimation' => $tache->getEstimation(),
            'debut' => $tache->getDebut()->format('Y-m-d H:i:s'),
        ]);
    }

    public function replanifier(Tache $tache): void {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "INSERT INTO historique_tache(debut, date_echeance, id_tache, estimation) VALUES (:debut, :echeance, :id_tache, :estimation)";
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement([
            'debut' => $tache->getDebut()?->format('Y-m-d H:i:s'),
            'echeance' => $tache->getDateEcheance()?->format('Y-m-d H:i:s'),
            'id_tache' => $tache->getId(),
            'estimation' => $tache->getEstimation(),
        ]);
    }

    public function get_taches_retards(Utilisateur $utilisateur): array {
        $conn = $this->getEntityManager()->getConnection();

        $day = new \DateTime();

        // $sql = "
        //     SELECT * 
        //     FROM tache t
        //     WHERE t.id_utilisateur = :id_utilisateur
        //     AND t.debut BETWEEN :dayStart AND :dayEnd
        //     AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
        //     ORDER BY t.debut ASC
        // ";

        $sql = "SELECT 
                    t.id_tache,
                    t.tache,
                    COALESCE(h.debut, t.debut) AS debut,
                    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
                    t.id_utilisateur,
                    t.id_activite,
                    COALESCE(h.estimation, t.estimation) AS estimation
                FROM tache t
                LEFT JOIN historique_tache h ON t.id_tache = h.id_tache
                WHERE t.id_utilisateur = :id_utilisateur
                AND COALESCE(h.debut, t.debut) BEFORE :jour
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_terminee)
                AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
                ORDER BY COALESCE(h.debut, t.debut) ASC";

        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'id_utilisateur' => $utilisateur->getId(),
            'jour' => $day
        ])->fetchAllAssociative();

        $taches = [];
        foreach ($results as $row) {
            $taches[] = $this->find($row['id_tache']);
        }

        return $taches;
    }

    public function supprimer(int $id_tache): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "INSERT INTO tache_supprimee (id_tache, date_suppression) 
                VALUES (:id_tache, :date_suppression)";
        
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement([
            'id_tache' => $id_tache,
            'date_suppression' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

}
