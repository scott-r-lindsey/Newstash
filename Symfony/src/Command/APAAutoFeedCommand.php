<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\BaseCommand;
use App\Service\Apa\Broker;
use App\Service\Apa\ProductApi;
use App\Service\Apa\ProductParser;
use App\Service\Data\EditionLeadPicker;
use DOMDocument;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class APAAutoFeedCommand extends BaseCommand
{
    private $productApi;
    private $productParser;
    private $editionLeadPicker;
    private $APABroker;
    private $em;

    public function __construct(
        EntityManagerInterface $em,
        ProductApi $productApi,
        ProductParser $productParser,
        EditionLeadPicker $editionLeadPicker,
        Broker $apaBroker
    )
    {
        $this->em                   = $em;
        $this->productApi           = $productApi;
        $this->productParser        = $productParser;
        $this->editionLeadPicker    = $editionLeadPicker;
        $this->apaBroker            = $apaBroker;

        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName('newstash:apa-auto-feed')
            ->setDescription('Fetches all the books')
            ->setHelp('This is the help')
            ->addOption(
                'fork',
                null,
                InputOption::VALUE_NONE,
                'Fork processing jobs to the background')
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $asins =  $this->editionLeadPicker->find();
        $output->writeln('<info>Found '. count($asins) . ' leads</info>');

        if ($input->getOption('fork')){
            $this->apaBroker->setFork(true);
        }

        while (count($asins)) {
            foreach ($asins as $asin) {
                $this->apaBroker->enqueue($asin);
            }
            $asins =  $this->editionLeadPicker->find();
        }
    }
}

