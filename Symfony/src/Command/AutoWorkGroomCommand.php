<?php
declare(strict_types=1);

namespace App\Command;


use App\Command\BaseCommand;
use App\Service\Data\WorkGroomer;
use App\Service\Data\EditionLeadPicker;
use Doctrine\ORM\EntityManagerInterface;
use Seld\Signal\SignalHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutoWorkGroomCommand extends BaseCommand
{
    private $em;
    private $editionLeadPicker;
    private $workGroomer;

    public function __construct(
        EntityManagerInterface $em,
        EditionLeadPicker $editionLeadPicker,
        WorkGroomer $workGroomer
    )
    {
        $this->em                   = $em;
        $this->editionLeadPicker    = $editionLeadPicker;
        $this->workGroomer          = $workGroomer;

        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName('newstash:apa-auto-workgroomer')
            ->setDescription('Continously scans for un-groomed editions')
            ->setHelp('This is the help');
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){

        $signal = SignalHandler::create(
            ['SIGINT'],
            function ($signal, $signalName) use ($output){
                $output->writeln("<info>Caught signal $signalName, shutting down...</info>");
        });

        ini_set('memory_limit', '2948M');

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);


        // --------------------------------------------------------------------

        while ($asin = $this->editionLeadPicker->findGroomable()) {
            $output->writeln("<info>Grooming http://amzn.com/$asin</info>");
            $this->workGroomer->workGroomLogic($asin);

            if ($signal->isTriggered()) {

                $output->writeln('<info>Exited cleanly.</info>');
                break;
            }
        }
    }
}

