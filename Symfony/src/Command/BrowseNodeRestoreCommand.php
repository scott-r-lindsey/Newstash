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

class BrowseNodeRestoreCommand extends BaseCommand
{
    private $api;
    private $broker;
    private $projectDir;

    public function __construct(
        ProductApi $api,
        Broker $broker,
        string $projectDir
    )
    {
        $this->api              = $api;
        $this->broker           = $broker;
        $this->projectDir       = $projectDir;

        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName('newstash:browsenode-restore')
            ->setDescription('Mysqldump the browsenode tables')
            ->setHelp('This is the help')
        ;

    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){
        $snapshot       = $this->projectDir . '/var/browsenodes-snap.sql';

        $database_url   = getenv('DATABASE_URL');

        preg_match('|mysql://([^:]+):([^@]+)@([^:]+):[\d]+/(.*)|', $database_url, $matches);
        list($str, $user, $pass, $host, $db) = $matches;

        if ($pass){
            $pass = "-p$pass";
        }

        $output->writeln("Restoring browsenode and browsenode_browsenode from $snapshot");
        exec("mysql -u $user -h $host $pass $db <$snapshot");
        $output->writeln('Done!');
    }
}
