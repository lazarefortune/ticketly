<?php

namespace App\Command;

use App\Domain\Event\Entity\Reservation;
use App\Infrastructure\Orm\CleanableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:minute-cron', description: 'Commande de nettoyage minutieux')]
class MinuteCronCommand extends Command
{

    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->setDescription( 'Commande de nettoyage' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) : int
    {
        $io = new SymfonyStyle( $input, $output );
        $this->cron( $io, $input, $output );
        $this->clean( $io, Reservation::class, 'expired reservations');
        return Command::SUCCESS;
    }

    public function cron( SymfonyStyle $io, InputInterface $input, OutputInterface $output ) : void
    {
        $io->success( 'Cron minutieux' );
    }

    /**
     * @throws Exception
     */
    private function clean( SymfonyStyle $io, string $entity, string $noun, string $cleanMethod = 'clean' ) : void
    {
        $repository = $this->em->getRepository( $entity );
        if ( !$repository instanceof CleanableRepositoryInterface ) {
            throw new Exception( sprintf( 'Le repository %s n\'implémente pas l\'interface CleanableRepositoryInterface', $entity ) );
        }
        $count = $repository->$cleanMethod();
        $io->success( sprintf( 'Delete %d %s', $count, $noun ) );
    }

}