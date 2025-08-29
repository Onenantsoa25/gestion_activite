<?php
namespace App\Service;

use App\Repository\UtilisateurRepository;
use App\Entity\Utilisateur;
use App\Entity\Role;

class UtilisateurService
{
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(UtilisateurRepository $utilisateurRepository) {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function findAllByRole(Role $role): array {
        return $this->utilisateurRepository->findAllByRole($role);
    }

    public function findById($id): ?Utilisateur {
        return $this->utilisateurRepository->findById($id);
    }

    // public function charges_travail(Utilisateur $utilisateur): void {
    //     $charge = $this->utilisateurRepository->charge_travail($utilisateur);
    //     $utilisateur->setCharges($charge);
    // }

}