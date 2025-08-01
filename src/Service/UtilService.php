<?php
namespace App\Service;

use App\Repository\UtilRepository;
use App\Entity\Util;

class UtilService
{
    private UtilRepository $utilRepository;

    public function __construct(UtilRepository $utilRepository)
    {
        $this->utilRepository = $utilRepository;
    }

    public function findAllUtil(): array
    {
        return $this->utilRepository->findAll();
    }

    public function insert(Util $util): void {
        $this->utilRepository->insert($util);
    }
}
