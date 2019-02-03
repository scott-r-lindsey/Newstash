<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\BaseCommand;
use App\Service\Apa;
use App\Service\Apa\Broker;
use App\Service\Apa\ProductApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DOMDocument;

class APATop10BooksPerNodeCommand extends BaseCommand
{
    private $api;
    private $broker;

    public function __construct(
        ProductApi $api,
        Broker $broker
    )
    {
        $this->api              = $api;
        $this->broker           = $broker;

        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName('newstash:apa-top-10-books-per-node')
            ->setDescription('Fetches the top 100 books')
            ->setHelp('This is the help')
            ->addOption(
                'output-only',
                null,
                InputOption::VALUE_NONE,
                'Just show me the XML and then exit'
            )
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){
        ini_set('memory_limit', '1024M');

        $output->writeln('Requesting from Top Sellers from APA...');
        $sxe = $this->api->topSellerBrowsenodeLookup(Apa::NODE_BOOKS);


        //FIXME this needs to recurse the browsenodes and grab top
        // books for each browsenode, not just the base ten

        if ($input->getOption('output-only')){

            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($sxe->asXML());

            $output->writeln($dom->saveXML());
        }
        else{

            $asins = [];
            foreach ($sxe->BrowseNodes->BrowseNode->TopSellers->TopSeller as $t) {
                $asins[] = (string)$t->ASIN;
            }

            dump($asins);



            $output->writeln('Done!');
        }
    }
}

