<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\BaseCommand;
use App\Entity\BrowseNode;
use App\Service\Apa;
use App\Service\Apa\Broker;
use App\Service\Apa\ProductApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;


class APATop10BooksPerNodeCommand extends BaseCommand
{
    private $em;
    private $api;
    private $broker;

    public function __construct(
        EntityManagerInterface $em,
        ProductApi $api,
        Broker $broker
    )
    {
        $this->em               = $em;
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

        if ($input->getOption('output-only')){

            $output->writeln('Requesting from Top Sellers from APA...');
            $sxe = $this->api->topSellerBrowsenodeLookup(Apa::NODE_BOOKS);

            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($sxe->asXML());

            $output->writeln($dom->saveXML());
        }
        else{

            $nodes = [Apa::NODE_BOOKS];
            $seen = [];

            $rootBrowseNode = $this->em->getRepository(BrowseNode::class)
                ->findOneById(Apa::NODE_BOOKS);

            foreach ($rootBrowseNode->getChildren() as $b) {
                $nodes[] = (int)$b->getId();
            }

            foreach ($nodes as $id) {

                $sxe = $this->api->topSellerBrowsenodeLookup($id);

                foreach ($sxe->BrowseNodes->BrowseNode->TopSellers->TopSeller as $t) {
                    if (!isset($seen[(string)$t->ASIN])) {
                        $this->broker->enqueue((string)$t->ASIN);
                        $seen[(string)$t->ASIN] = true;
                    }
                }
            }

            $this->broker->process();

            $output->writeln('Done!');
        }
    }
}

