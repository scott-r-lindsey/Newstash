<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\Export;
use App\Command\BaseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MongoExportCommand extends BaseCommand
{
    private $export;

    public function __construct(
        Export $export
    )
    {
        $this->export           = $export;

        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName('newstash:mongo:export')
            ->setDescription('Export data from MySQL to Mongo')
            ->setHelp('This is the help')
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){

        $this->export->setOutput($output);

        $output->writeln('<info>Starting MySQL to Mongo export </info>');
        $this->export->export();
        $output->writeln('<info>DONE!</info>');
    }
}

