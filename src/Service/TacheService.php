<?php

namespace App\Service;

use App\Repository\TacheRepository;
use App\Entity\Tache;
use App\Entity\Activite;
use App\Entity\Notification;
use App\Entity\TacheTerminee;
use App\Entity\TypeActivite;
use App\Entity\Utilisateur;
use App\Repository\NotificationRepository;
use App\Repository\TacheTermineeRepository;
use DateTime;
use Exception;

class TacheService
{
    private TacheRepository $tacheRepository;
    private NotificationRepository $notificationRepository;
    private TacheTermineeRepository $tacheTermineeRepository;

    public function __construct(TacheRepository $tacheRepository, NotificationRepository $notificationRepository, TacheTermineeRepository $tacheTermineeRepository)
    {
        $this->tacheRepository = $tacheRepository;
        $this->notificationRepository = $notificationRepository;
        $this->tacheTermineeRepository = $tacheTermineeRepository;
    }

    public function insertion_manager(?Tache $tache): void
    {
        if ($tache) {
            $this->tacheRepository->insertion_manager($tache);
        }
    }

    public function findAllByActivite(?Activite $activite): array
    {
        return $this->tacheRepository->findAllByActivite($activite);
    }

    public function findById($id): ?Tache
    {
        return $this->tacheRepository->findById($id);
    }

    public function attribuer(Tache $tache, Utilisateur $utilisateur): void
    {
        $this->tacheRepository->attribuer($tache->getId(), $utilisateur->getId());
    }

    public function findAllByUser(Utilisateur $utilisateur): array
    {
        return $this->tacheRepository->findAllByUser($utilisateur);
    }

    public function activite_utilisateur(Utilisateur $utilisateur, Activite $activite): array
    {
        $taches = $this->tacheRepository->findAllByActivite($activite);
        $resultat = [];
        foreach ($taches as $tache) {
            if ($tache->getUtilisateur() && $tache->getUtilisateur()->getId() == $utilisateur->getId()) {
                $resultat[] = $tache;
            }
        }

        return $resultat;
    }

    public function terminer(int $id_tache, DateTime $date, float $temps_passe): void
    {
        $this->tacheRepository->terminer($id_tache, $date, $temps_passe);
    }

    public function est_terminee(Tache $tache): void
    {
        $tache->setTerminee($this->tacheRepository->est_terminee($tache->getId()));
    }

    public function getTemps_passee(Tache $tache): void
    {
        $temps = $this->tacheRepository->getTemps_passee($tache->getId());
        $tache->setTemps_passe($temps);
    }

    public function getAllActivite_collab(Utilisateur $utilisateur, Activite $activite): array
    {
        $taches = $this->activite_utilisateur($utilisateur, $activite);
        foreach ($taches as $tache) {
            $this->est_terminee($tache);
            $this->getTemps_passee($tache);
        }
        return $taches;
    }

    public function planifier(Tache $tache, DateTime $dateDebut/*, DateTime $dateFin*/): void
    {
        $this->tacheRepository->planifier($tache->getId(), $dateDebut/*, $dateFin*/);
        $notification = $this->notificationRepository->findMissionByTache($tache->getId());
        // if (!$notification->isEstLue()) {
        $notification->setEstLue(true);
        $this->notificationRepository->marquerCommeLue($notification);
        // }
    }

    public function findTachesDuJourParUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->tacheRepository->findTachesDuJour($utilisateur);
    }

    public function findPlanningDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate, Utilisateur $utilisateur): array
    {
        return $this->tacheRepository->findPlanningDates($startDate, $endDate, $utilisateur);
    }

    public function findPlanningJour(\DateTimeInterface $date, Utilisateur $utilisateur): array
    {
        return $this->tacheRepository->findTachesParUtilisateurEtDate($utilisateur, $date);
    }

    public function tachesNonTerminees(Utilisateur $utilisateur): array
    {
        return $this->tacheRepository->tachesNonTerminees($utilisateur);
    }

    public function tachesNonTermineesActivite(Utilisateur $utilisateur, TypeActivite $activite): array
    {
        return $this->tacheRepository->tachesNonTermineesActivite($utilisateur, $activite);
    }

    public function tempsPasseAujourdHui(Utilisateur $utilisateur): float
    {
        return $this->tacheRepository->tempsPasseAujourdHui($utilisateur);
    }

    public function tempsPasseSemaine(Utilisateur $utilisateur): float
    {
        return $this->tacheRepository->tempsPasseSemaine($utilisateur);
    }

    public function countTachesEnCours(Utilisateur $utilisateur): int {
        return $this->tacheRepository->countTachesEnCours($utilisateur);
    }

    public function countTachesAFaire(Utilisateur $utilisateur): int {
        return $this->tacheRepository->countTachesAFaire($utilisateur);
    }

    public function countTachesTermineesAujourdHui(Utilisateur $utilisateur): int {
        return $this->tacheRepository->countTachesTermineesAujourdHui($utilisateur);
    }

    public function getJoursOuvrablesSemaine(): array
    {
        $jours = [];
        $aujourdhui = new \DateTime('today'); // DateTime mutable

        // Lundi de cette semaine
        $lundi = (clone $aujourdhui)->modify('monday this week');

        // On parcourt du lundi au vendredi
        for ($i = 0; $i < 5; $i++) {
            $jour = (clone $lundi)->modify("+{$i} day");
            // On ne prend que les jours >= aujourd'hui
            if ($jour >= $aujourdhui) {
                $jours[] = $jour; // DateTime qui implémente DateTimeInterface
            }
        }

        return $jours; // tableau de DateTimeInterface
    }

    public function charges_semaine(Utilisateur $utilisateur): array {
        $charges = [];
        $dates = $this->getJoursOuvrablesSemaine();
        foreach ($dates as $date) {
            $nb = $this->tacheRepository->countTachesNonTermineesParDate($utilisateur, $date);
            $charges[] = [
                'date' => $date->format('Y-m-d'),
                'charges' => $nb
            ];
        }
        return $charges;
    }

    public function terminerTache(int $id_tache, \DateTimeInterface $dateTerminee, float $tempsPasse, string $commentaire, $justificatif): void {
        // $reussi = $this->tacheRepository->terminerTache($id_tache, $dateTerminee, $tempsPasse);
        // if(!$reussi){
        //     throw new Exception("Tache deja terminee ou tache qui n'existe pas!!");
        // }
        $tache_terminee = new TacheTerminee($dateTerminee, $tempsPasse, $this->findById($id_tache), $commentaire, $justificatif);
        $this->tacheTermineeRepository->save($tache_terminee, true);
    }

    public function tempsPasseAujourdHui_equipe(): float
    {
        return $this->tacheRepository->tempsPasseAujourdHui_equipe();
    }

    public function tempsPasseSemaine_equipe(): float
    {
        return $this->tacheRepository->tempsPasseSemaine_equipe();
    }

    public function countTachesEnCours_equipe(): int {
        return $this->tacheRepository->countTachesEnCours_equipe();
    }

    public function countTachesAFaire_equipe(): int {
        return $this->tacheRepository->countTachesAFaire_equipe();
    }

    public function countTachesTermineesAujourdHui_equipe(): int {
        return $this->tacheRepository->countTachesTermineesAujourdHui_equipe();
    }

    public function findByType_activite(int $id_type): array {
        return $this->tacheRepository->findByType($id_type);
    }

    // public function rapportDates(Utilisateur $utilisateur, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array{
    //     return $this->tacheTermineeRepository->rapportDates($utilisateur, $dateDebut, $dateFin);
    // }
    
    public function rapportDates(Utilisateur $utilisateur, ?\DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin): array
    {
        $aujourdhui = new \DateTimeImmutable('today');

        // Cas 1 : les deux dates sont nulles → semaine en cours
        if ($dateDebut === null && $dateFin === null) {
            $lundi = $aujourdhui->modify('monday this week');
            $dimanche = $aujourdhui->modify('sunday this week');
            $dateDebut = $lundi;
            $dateFin = $dimanche;
        }
        // Cas 2 : seulement la date de début est fournie → semaine de cette date
        elseif ($dateDebut !== null && $dateFin === null) {
            $dateDebut = (new \DateTimeImmutable($dateDebut->format('Y-m-d')))->modify('monday this week');
            $dateFin = (new \DateTimeImmutable($dateDebut->format('Y-m-d')))->modify('sunday this week');
        }
        // Cas 3 : seulement la date de fin est fournie → semaine de cette date
        elseif ($dateDebut === null && $dateFin !== null) {
            $dateDebut = (new \DateTimeImmutable($dateFin->format('Y-m-d')))->modify('monday this week');
            $dateFin = (new \DateTimeImmutable($dateFin->format('Y-m-d')))->modify('sunday this week');
        }

        return $this->tacheTermineeRepository->rapportDates($utilisateur, $dateDebut, $dateFin);
    }

    public function replanifier(int $id_tache, \DateTimeInterface $dateDebut): void {
        $tache = $this->tacheRepository->findById($id_tache);
        $tache->setDebut($dateDebut);
        $this->tacheRepository->replanifier($tache);
    }

    public function modifier(Tache $tache): void {
        $this->tacheRepository->modifier($tache);
    }

    public function supprimer(int $id_tache): void {
        $this->tacheRepository->supprimer($id_tache);
    }

}
