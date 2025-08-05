<?php
namespace App\Service\test;

use App\Repository\test\UtilRepository;
use App\Entity\test\Util;

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
