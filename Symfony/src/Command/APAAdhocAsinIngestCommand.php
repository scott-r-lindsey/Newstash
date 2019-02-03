<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\BaseCommand;
use App\Service\Apa;
use App\Service\Apa\ProductParser;
use App\Service\Apa\ProductApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DOMDocument;

class APAAdhocAsinIngestCommand extends BaseCommand
{
    private $api;
    private $parser;

    public function __construct(
        ProductApi $api,
        ProductParser $parser
    )
    {
        $this->api              = $api;
        $this->parser           = $parser;

        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName('newstash:apa-adhoc-asin-ingest')
            ->setDescription('Fetches a book')
            ->setHelp('This is the help')
            ->addArgument(
                'asin',
                InputArgument::REQUIRED,
                'The asin to query?'
            )
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

        $asin = $input->getArgument('asin');

        $output->writeln('Requesting from $asin from APA...');
        $sxe = $this->api->ItemLookup(
            [$asin],
            Apa::STANDARD_RESPONSE_TYPES
        );

        if ($input->getOption('output-only')){

            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($sxe->asXML());

            $output->writeln($dom->saveXML());
        }
        else{
            $output->writeln('Parsing and ingesting data...');

            $this->parser->ingest($sxe->Items->Item[0]);

            $output->writeln('Done!');
        }
    }
}

