<?php
namespace App\Service;

use App\Repository\AnomalieRepository;

class AnomalieService
{
    private AnomalieRepository $anomalieRepository;

    public function __construct(AnomalieRepository $anomalieRepository) 
    {
        $this->anomalieRepository = $anomalieRepository;
    }

    public function findNonResolue(): array {
        $this->anomalieRepository->analyse_oublie();
        $this->anomalieRepository->analyse_surcharge();
        $this->anomalieRepository->analyse_sous_activite();
        return $this->anomalieRepository->findNonResolues();
    }

    public function resoudre(int $id): void {
        $this->anomalieRepository->resoudre($id);
    }

    public function updateAnomalies(): void {
        $this->anomalieRepository->update_anomalies();
    }

    public function countNonResolues(): int {
        return $this->anomalieRepository->countNonResolues();
    }

    public function analyse(): void {
        $this->anomalieRepository->analyse_oublie();
    }

}