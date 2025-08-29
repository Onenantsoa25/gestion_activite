<?php

namespace App\Repository;

// use App\Entity\Activite;
use App\Entity\Notification;
use App\Entity\Tache;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeActivite>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.utilisateur = :utilisateur')
            ->andWhere('n.estLue = :estLue')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('estLue', false)
            ->orderBy('n.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countNonLuesByUtilisateur(Utilisateur $utilisateur): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.idNotification)')
            ->andWhere('n.utilisateur = :utilisateur')
            ->andWhere('n.estLue = :estLue')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('estLue', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMissionByTache(int $idTache): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.tache = :tache')
            ->andWhere('n.typeNotif = :type')
            ->setParameter('tache', $idTache)
            ->setParameter('type', 'mission')
            ->orderBy('n.dateCreation', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function marquerCommeLue(Notification $notification): void
    {
        $notification->setEstLue(true);
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();
    }
}
