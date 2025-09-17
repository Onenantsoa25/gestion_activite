<?php
// src/Repository/TypeActiviteRepository.php
namespace App\Repository;

use App\Entity\Anomalie;
use App\Entity\Tache;
use App\Entity\TypeAnomalie;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeActivite>
 */
class AnomalieRepository extends ServiceEntityRepository
{
    private const MAX_TACHES = 8;
    private const MIN_TACHE = 2;
    private TacheRepository $tacheRepository;

    public function __construct(ManagerRegistry $registry, TacheRepository $tacheRepository)
    {
        parent::__construct($registry, Anomalie::class);
        $this->tacheRepository = $tacheRepository;
    }

    // public function findByUtilisateur(int $id_utilisateur)
    public function analyse_oublie(): void{
        $conn = $this->getEntityManager()->getConnection(); 

        $sql = "SELECT t.id_tache, t.tache, t.date_echeance FROM v_echeance_taches t where t.id_tache not in (SELECT id_tache from tache_terminee) AND t.date_echeance <= NOW() AND t.id_tache not in (SELECT id_tache from anomalie WHERE DATE(date_anomalie) = CURDATE() AND id_tache IS NOT NULL) AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->execute();
        $rows = $resultSet->fetchAllAssociative();
        $em = $this->getEntityManager(); // récupérer EntityManager une seule fois
        $typeRepository = $em->getRepository(TypeAnomalie::class);
        // $tacheRepository =(TacheRepository) $em->getRepository(Tache::class);
        // $tacheRepository = new TacheRepository($em);
        $type = $typeRepository->find(1);
        foreach ($rows as $row) {
            $anomalie = new Anomalie();
            $anomalie->setDateAnomalie(new \DateTime());
            $anomalie->setTypeAnomalie($type);
            $tache = $this->tacheRepository->findById($row['id_tache']);

            $utilisateur = $tache->getUtilisateur();
            // $rep = [];
            if($utilisateur !== null){
                $matricule = $utilisateur ? $utilisateur->getMatricule() : 'Inconnu';

                // echo "utilisateur 11".$matricule;
                // dump($tache);
                // die();
                $message = "Oubli de saisie de la tache : '".$row['tache']."' du collaborateur : ".$matricule."\n \t Date d'echeance : ".$tache->getDateEcheance()->format('d/m/Y H:i');
                $anomalie->setMessage($message);
                $anomalie->setUtilisateur($tache->getUtilisateur());
                $anomalie->setTache($tache);
                $anomalie->setEstResolue(false);
                $em->persist($anomalie);
                $em->flush();
            }

            // $rep[] = $anomalie;
            // $anomalie->setTypeAnomalie()
        }
        // return $rep;
    }

    // public function analyse_surcharge(): void {
    //     // $rep = [];

    //     $conn = $this->getEntityManager()->getConnection();

    //     $sql = "SELECT u.id_utilisateur, SUM(t.estimation) AS total_heure 
    //             FROM v_tache_non_terminee t JOIN utilisateur u ON u.id_utilisateur = t.id_utilisateur 
    //             WHERE DATE(t.debut) = CURDATE() 
    //             AND t.id_tache NOT IN (SELECT id_tache from tache_terminee) 
    //             AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
    //             AND u.id_utilisateur NOT IN (SELECT id_utilisateur FROM anomalie WHERE DATE(date_anomalie) = CURDATE() AND id_type_anomalie = 2 AND est_resolue = 1) 
    //             GROUP BY u.id_utilisateur 
    //             HAVING total_heure >= :max_taches";

    //     $stmt = $conn->prepare($sql);
    //     $resultSet = $stmt->execute([
    //         'max_taches' => self::MAX_TACHES,
    //     ]);
    //     $rows = $resultSet->fetchAllAssociative();
    //     $em = $this->getEntityManager();
    //     $utilisateurRepository = $em->getRepository(Utilisateur::class);
    //     $typeRepository = $em->getRepository(TypeAnomalie::class);
    //     $type = $typeRepository->find(2);
    //     foreach ($rows as $row) {
    //         $anomalie = new Anomalie();
    //         $anomalie->setDateAnomalie(new \DateTime());
    //         $anomalie->setTypeAnomalie($type);
    //         $utilisateur = $utilisateurRepository->findById($row['id_utilisateur']);
    //         $anomalie->setUtilisateur($utilisateur);
    //         $message = "Surcharge de tache pour le collaborateur : ".$utilisateur->getMatricule()."\n Total heure : ".$row['total_heure'];
    //         $anomalie->setMessage($message);
    //         $anomalie->setEstResolue(false);
    //         $em->persist($anomalie);
    //         $em->flush();
    //         // $rep[] = $anomalie;
    //     }
    //     // return $rep;
    // }

    public function analyse_sous_activite(): void {
        // $rep = [];

        $conn = $this->getEntityManager()->getConnection();

        // $sql = "SELECT u.id_utilisateur, SUM(t.estimation) AS total_heure 
        //         FROM v_tache_non_terminee t JOIN utilisateur u ON u.id_utilisateur = t.id_utilisateur 
        //         WHERE DATE(t.debut) = CURDATE() 
        //         AND t.id_tache NOT IN (SELECT id_tache from tache_terminee) 
        //         AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
        //         AND u.id_utilisateur NOT IN (SELECT id_utilisateur FROM anomalie WHERE DATE(date_anomalie) = CURDATE() AND id_type_anomalie = 3 AND est_resolue = 1) 
        //         GROUP BY u.id_utilisateur 
        //         HAVING total_heure <= :min_taches";

        $sql = "SELECT * FROM v_tache_non_terminee_utilisateur WHERE id_utilisateur NOT IN (SELECT id_utilisateur FROM anomalie WHERE DATE(date_anomalie) = CURDATE() AND id_type_anomalie = 3 AND est_resolue = 0) AND id_utilisateur IS NOT NULL";

        $stmt = $conn->prepare($sql);
        // $resultSet = $stmt->execute([
        //     'min_taches' => self::MIN_TACHE
        // ]);
        $resultSet = $stmt->execute();
        $rows = $resultSet->fetchAllAssociative();
        $em = $this->getEntityManager();
        $utilisateurRepository = $em->getRepository(Utilisateur::class);
        $typeRepository = $em->getRepository(TypeAnomalie::class);
        $type = $typeRepository->find(3);
        $type_sur = $typeRepository->find(2);
        foreach ($rows as $row) {
            $anomalie = new Anomalie();
            $anomalie->setDateAnomalie(new \DateTime());
            $utilisateur = $utilisateurRepository->find($row['id_utilisateur']);
            if(($utilisateur) !== null) {
                $anomalie->setUtilisateur($utilisateur);
                if( $row['estimation'] <= self::MIN_TACHE ) {
                    $message = "Sous-activite pour le collaborateur : ".$utilisateur->getMatricule()."\n Total heure : ".$row['estimation'];
                    $anomalie->setTypeAnomalie($type);
                    $anomalie->setMessage($message);
                    $anomalie->setEstResolue(false);
                    $em->persist($anomalie);
                    $em->flush();
                }

            }

            // $rep[] = $anomalie;
        }
        // return $rep;
    }

    public function analyse_surcharge(): void {
        // $rep = [];

        $conn = $this->getEntityManager()->getConnection();

        // $sql = "SELECT u.id_utilisateur, SUM(t.estimation) AS total_heure 
        //         FROM v_tache_non_terminee t JOIN utilisateur u ON u.id_utilisateur = t.id_utilisateur 
        //         WHERE DATE(t.debut) = CURDATE() 
        //         AND t.id_tache NOT IN (SELECT id_tache from tache_terminee) 
        //         AND t.id_tache NOT IN (SELECT id_tache FROM tache_supprimee)
        //         AND u.id_utilisateur NOT IN (SELECT id_utilisateur FROM anomalie WHERE DATE(date_anomalie) = CURDATE() AND id_type_anomalie = 3 AND est_resolue = 1) 
        //         GROUP BY u.id_utilisateur 
        //         HAVING total_heure <= :min_taches";

        $sql = "SELECT * FROM v_tache_non_terminee_utilisateur WHERE id_utilisateur NOT IN (SELECT id_utilisateur FROM anomalie WHERE DATE(date_anomalie) = CURDATE() AND id_type_anomalie = 2 AND est_resolue = 0) AND id_utilisateur IS NOT NULL";

        $stmt = $conn->prepare($sql);
        // $resultSet = $stmt->execute([
        //     'min_taches' => self::MIN_TACHE
        // ]);
        $resultSet = $stmt->execute();
        $rows = $resultSet->fetchAllAssociative();
        $em = $this->getEntityManager();
        $utilisateurRepository = $em->getRepository(Utilisateur::class);
        $typeRepository = $em->getRepository(TypeAnomalie::class);
        $type = $typeRepository->find(3);
        $type_sur = $typeRepository->find(2);
        foreach ($rows as $row) {
            $anomalie = new Anomalie();
            $anomalie->setDateAnomalie(new \DateTime());
            $utilisateur = $utilisateurRepository->find($row['id_utilisateur']);
            if(($utilisateur) !== null) {
                $anomalie->setUtilisateur($utilisateur);
                if( $row['estimation'] >= self::MAX_TACHES ){
                    // Déjà une anomalie de surcharge, on ne crée pas une nouvelle anomalie de sous-activité
                    $message = "Surcharge de tache pour le collaborateur : ".$utilisateur->getMatricule()."\n Total heure : ".$row['estimation'];
                    $anomalie->setTypeAnomalie($type);
                    $anomalie->setMessage($message);
                    $anomalie->setEstResolue(false);
                    $em->persist($anomalie);
                    $em->flush();
                    // continue;
                }

            }

            // $rep[] = $anomalie;
        }
        // return $rep;
    }

    public function findNonResolues(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.estResolue = :resolue')
            ->setParameter('resolue', false)
            ->orderBy('a.dateAnomalie', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function resoudre(int $idAnomalie): void
    {
        $em = $this->getEntityManager();
        $anomalie = $this->find($idAnomalie);

        if ($anomalie) {
            $anomalie->setEstResolue(true);
            $em->persist($anomalie);
            $em->flush();
        }
    }

    public function update_anomalies(): void {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "UPDATE anomalie
                SET est_resolue = TRUE
                WHERE DATE(date_anomalie) = CURDATE() - INTERVAL 1 DAY AND (id_type_anomalie = 2 OR id_type_anomalier = 3)";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    public function countNonResolues(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.idAnomalie)')
            ->andWhere('a.estResolue = :resolue')
            ->setParameter('resolue', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

}