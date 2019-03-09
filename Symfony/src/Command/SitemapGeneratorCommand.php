<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\BaseCommand;
use App\Service\SitemapGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapGeneratorCommand extends BaseCommand
{
    private $em;

    public function __construct(
        SitemapGenerator $sg
    )
    {
        $this->sg                   = $sg;

        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName('newstash:generate-sitemap')
            ->setDescription('Generates the sitemap xml')
            ->setHelp('This is the help');
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){
        $this->sg->setOutput($output);

        $output->writeln('starting...');
        $files = $this->sg->generateSitemap();
        print_r($files);
    }
}

