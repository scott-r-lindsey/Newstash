<?php
declare(strict_types=1);

namespace App\Service;

use App\Lib\OutputingService;
use App\Service\Mongo;
use App\Service\Export\BrowseNode;
use App\Service\Export\Work;
use Doctrine\DBAL\Statement;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MongoDB;

class Export extends OutputingService
{
    private $logger;
    private $mongo;
    private $browseNodeExporter;
    private $workExporter;
    private $defaultMongoDb;

    public function __construct(
        LoggerInterface $logger,
        Mongo $mongo,
        BrowseNode $browseNodeExporter,
        Work $workExporter,
        string $defaultMongoDb
    )
    {
        $this->logger               = $logger;
        $this->mongo                = $mongo;
        $this->browseNodeExporter   = $browseNodeExporter;
        $this->workExporter         = $workExporter;
        $this->defaultMongoDb       = $defaultMongoDb;
    }

    public function export(): void
    {
        $this->writeln('<comment>Dropping old collections...</comment>');

        $this->browseNodeExporter->setOutput($this->output);
        $this->workExporter->setOutput($this->output);

        $mongodb        = $this->mongo->getDb();

        //----------------------------------------------------------------------
        $mongodb->works_old->drop();
        $mongodb->works_new->drop();
        $mongodb->typeahead_work_old->drop();
        $mongodb->typeahead_work_new->drop();
        $mongodb->typeahead_author_old->drop();
        $mongodb->typeahead_author_new->drop();
        $mongodb->nodes_old->drop();
        $mongodb->nodes_new->drop();
        //----------------------------------------------------------------------

        $this->browseNodeExporter->exportNodes();
        $this->workExporter->exportWorks();

        //----------------------------------------------------------------------

        $this->writeln("<comment>Switching collections...</comment>");
        $this->switchCollection('nodes');
        $this->switchCollection('works');
        $this->switchCollection('typeahead_work');
        $this->switchCollection('typeahead_author');

        //----------------------------------------------------------------------

        $this->writeln("<comment>Dropping old collections...</comment>");
        $mongodb->works_old->drop();
        $mongodb->typeahead_work_old->drop();
        $mongodb->typeahead_author_old->drop();
        $mongodb->nodes_old->drop();
    }

    private function switchCollection(
        string $collection): void
    {
        $dbname         = $this->defaultMongoDb;
        $admindb        = $this->mongo->getDb('admin');

        $this->writeln("<comment>renaming $collection to $collection"."_old</comment>");
        $ret =  $admindb->command(array (
            'renameCollection'  =>  $dbname . ".$collection",
            'to'                =>  $dbname . ".$collection"."_old")
        );

        $this->writeln("<comment>renaming $collection"."_new to $collection</comment>");
        $ret =  $admindb->command(array (
            'renameCollection'  =>  $dbname . ".$collection"."_new",
            'to'                =>  $dbname . ".$collection")
        );
    }
}
