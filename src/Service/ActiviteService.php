<?php
namespace App\Service;

use App\Repository\ActiviteRepository;
use App\Entity\Activite;
use App\Entity\Utilisateur;
use App\Repository\TacheRepository;

class ActiviteService
{
    private ActiviteRepository $activiteRepository;
    private TacheRepository $tacheRepository;

    public function __construct(ActiviteRepository $activiteRepository, TacheRepository $tacheRepository) {
        $this->activiteRepository = $activiteRepository;
        $this->tacheRepository = $tacheRepository;
    }

    public function insertion_manager(?Activite $activite): void {
        if($activite) {
            $this->activiteRepository->insertion_manager($activite);
        }
    }

    public function findById(int $id): ?Activite {
        return $this->activiteRepository->find($id);
    }

    public function findAllByUser(Utilisateur $utilisateur): array {
        return $this->activiteRepository->findNonTermineeUtilisateur($utilisateur);
    }

    public function findAllNonTerminee(): array {
        return $this->activiteRepository->findAllNonTerminee();
    }

    public function tache_a_faires(): array {
        $nombre = [];
        $activites = $this->activiteRepository->findAllNonTerminee();

        foreach($activites as $activite) {
            $nombre[] = [
                'activite' => $activite,
                'nombre' => $this->tacheRepository->countTachesNonTermineesParActivite($activite),
            ];
        }

        return $nombre;
    }

    public function findAllNonValidees(): array {
        return $this->activiteRepository->findAllNonValidees();
    }

    public function valider(int $id): void {
        $this->activiteRepository->validerActivite($id);
    }

    public function supprimer(int $id): void {
        $this->activiteRepository->supprimerActivite($id);
    }

    public function commencer(int $id): void {
        $this->activiteRepository->commencer($id);
    }

    public function modifier(Activite $activite): void {
        $this->activiteRepository->modifier($activite);
    }

}