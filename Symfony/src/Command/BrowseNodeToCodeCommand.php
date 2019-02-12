<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\BaseCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DOMDocument;

class BrowseNodeToCodeCommand extends BaseCommand
{
    private $em;

    public function __construct(
        EntityManagerInterface $em,
        string $projectDir
    )
    {
        $this->em                   = $em;
        $this->projectDir           = $projectDir;

        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName('newstash:browsenode-to-code')
            ->setDescription('Export the browsenode tables to code')
            ->setHelp('This is the help')
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){


        $output->writeln('sup!');
    }
}

