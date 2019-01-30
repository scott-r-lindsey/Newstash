<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\BaseCommand;
use App\Service\Apa\Broker;
use App\Service\Apa\ProductParser;
use App\Service\Apa\ProductApi;
use App\Service\Data\EditionLeadPicker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DOMDocument;

class APAAutoFeedCommand extends BaseCommand
{
    private $productApi;
    private $productParser;
    private $editionLeadPicker;
    private $APABroker;

    public function __construct(
        ProductApi $productApi,
        ProductParser $productParser,
        EditionLeadPicker $editionLeadPicker,
        Broker $apaBroker
    )
    {
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
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){

        $asins =  $this->editionLeadPicker->find();
        $output->writeln('<info>Found '. count($asins) . ' leads</info>');

        while (count($asins)) {
            foreach ($asins as $asin) {
                $this->apaBroker->enqueue($asin);
            }
        }
    }
}

