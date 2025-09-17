<?php

// src/Command/EncodeUserPasswordCommand.php
namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:encode-passwords',
    description: 'Hash all plain-text user passwords stored in the database',
)]
class EncodeUserPasswordCommand extends Command
{
    private $em;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->em->getRepository(Utilisateur::class)->findAll();

        foreach ($users as $user) {
            $hashed = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashed);
            $output->writeln("Password hashed for user matricule ".$user->getMatricule());
        }

        $this->em->flush();

        $output->writeln('<info>All passwords have been encoded successfully!</info>');

        return Command::SUCCESS;
    }
}
