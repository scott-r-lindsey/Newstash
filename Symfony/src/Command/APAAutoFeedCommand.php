<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\BaseCommand;
use App\Service\Apa\ProductParser;
use App\Service\Apa\ProductApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DOMDocument;

class APAAutoFeedtCommand extends BaseCommand
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
            ->setName('newstash:apa-auto-feed')
            ->setDescription('Fetches all the books')
            ->setHelp('This is the help')
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){


        // needs a service to find leads, books to refresh

        // needs a service to schedule reads


    }
}

