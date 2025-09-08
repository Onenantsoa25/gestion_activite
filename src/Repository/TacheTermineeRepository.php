<?php

namespace App\Repository;

use App\Entity\TacheTerminee;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TacheTerminee>
 */
class TacheTermineeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TacheTerminee::class);
    }

    /**
     * Sauvegarde une entité
     */
    public function save(TacheTerminee $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Supprime une entité
     */
    public function remove(TacheTerminee $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Trouve les tâches terminées validées
     *
     * @return TacheTerminee[]
     */
    public function findAllValidees(): array
    {
        return $this->createQueryBuilder('tt')
            ->andWhere('tt.estValidee = :val')
            ->setParameter('val', true)
            ->orderBy('tt.dateTerminee', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches terminées par utilisateur
     *
     * @param int $utilisateurId
     * @return TacheTerminee[]
     */
    public function findByUtilisateur(int $utilisateurId): array
    {
        return $this->createQueryBuilder('tt')
            ->join('tt.tache', 't')
            ->join('t.utilisateur', 'u')
            ->andWhere('u.id = :idUser')
            ->setParameter('idUser', $utilisateurId)
            ->orderBy('tt.dateTerminee', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches terminées d'un utilisateur entre deux dates
     *
     * @param int $utilisateurId
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @return TacheTerminee[]
     */
    // public function rapportDates(Utilisateur $utilisateur, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    // {
    //     return $this->createQueryBuilder('tt')
    //         ->join('tt.tache', 't')
    //         ->join('t.utilisateur', 'u')
    //         ->andWhere('u.id = :idUser')
    //         ->andWhere('tt.dateTerminee BETWEEN :dateDebut AND :dateFin')
    //         ->setParameter('idUser', $utilisateur->getId())
    //         ->setParameter('dateDebut', $dateDebut->format('Y-m-d 00:00:00'))
    //         ->setParameter('dateFin', $dateFin->format('Y-m-d 23:59:59'))
    //         ->orderBy('tt.dateTerminee', 'DESC')
    //         ->getQuery()
    //         ->getResult();
    // }

    // public function rapportDates(Utilisateur $utilisateur, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    // {
    //     $taches = $this->createQueryBuilder('tt')
    //         ->join('tt.tache', 't')
    //         ->join('t.utilisateur', 'u')
    //         ->andWhere('u.id = :idUser')
    //         ->andWhere('tt.dateTerminee BETWEEN :dateDebut AND :dateFin')
    //         ->setParameter('idUser', $utilisateur->getId())
    //         ->setParameter('dateDebut', $dateDebut->format('Y-m-d 00:00:00'))
    //         ->setParameter('dateFin', $dateFin->format('Y-m-d 23:59:59'))
    //         ->orderBy('tt.dateTerminee', 'ASC')
    //         ->getQuery()
    //         ->getResult();

    //     // Regrouper par date
    //     $groupes = [];
    //     foreach ($taches as $tache) {
    //         /** @var TacheTerminee $tache */
    //         $dateCle = $tache->getDateTerminee()->format('d-m-Y');
    //         $groupes[$dateCle][] = $tache;
    //     }

    //     // Construire le format final
    //     $resultat = [];
    //     $total = 0;
    //     foreach ($groupes as $date => $listeTaches) {
    //         $total_jour = TacheTerminee::totalDuree($listeTaches);
    //         $resultat[] = [
    //             'date' => $date,
    //             'tache_terminee' => $listeTaches,
    //             'total_duree' => $total_jour
    //         ];
    //         $total += $total_jour;
    //     }

    //     return [
    //         'details' => $resultat,
    //         'total' => $total
    //     ];
    // }

public function rapportDates(Utilisateur $utilisateur, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
{
    $taches = $this->createQueryBuilder('tt')
        ->join('tt.tache', 't')
        ->join('t.utilisateur', 'u')
        ->andWhere('u.id = :idUser')
        ->andWhere('tt.dateTerminee BETWEEN :dateDebut AND :dateFin')
        ->setParameter('idUser', $utilisateur->getId())
        ->setParameter('dateDebut', $dateDebut->format('Y-m-d 00:00:00'))
        ->setParameter('dateFin', $dateFin->format('Y-m-d 23:59:59'))
        ->orderBy('tt.dateTerminee', 'ASC')
        ->getQuery()
        ->getResult();

    // Regrouper par date
    $groupes = [];
    foreach ($taches as $tache) {
        /** @var TacheTerminee $tache */
        $dateCle = $tache->getDateTerminee()->format('d-m-Y');
        $groupes[$dateCle][] = $tache;
    }

    // Générer toutes les dates entre début et fin
    $periode = new \DatePeriod(
        new \DateTime($dateDebut->format('Y-m-d')),
        new \DateInterval('P1D'),
        (new \DateTime($dateFin->format('Y-m-d')))->modify('+1 day')
    );

    // Construire le résultat final
    $resultat = [];
    $total = 0;

    foreach ($periode as $date) {
        // Ignorer samedi (6) et dimanche (0)
        $jourSemaine = (int) $date->format('w'); // 0 = dimanche, 6 = samedi
        if ($jourSemaine === 0 || $jourSemaine === 6) {
            continue;
        }

        $cle = $date->format('d-m-Y');
        $listeTaches = $groupes[$cle] ?? [];
        $total_jour = empty($listeTaches) ? 0 : TacheTerminee::totalDuree($listeTaches);

        $resultat[] = [
            'date' => $cle,
            'tache_terminee' => $listeTaches,
            'total_duree' => $total_jour
        ];
        $total += $total_jour;
    }

    return [
        'details' => $resultat,
        'total' => $total
    ];
}


}
