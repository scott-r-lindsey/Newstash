<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\BookParser;

class ReadBooksCommand extends Command
{

    private $BookParser;

    public function __construct(BookParser $bookParser)
    {
        $this->BookParser = $bookParser;

        parent::__construct();
    }


    protected function configure()
    {

        $this
            ->setName('newstash:read-books')
            ->setDescription('Fetches some books')
            ->setHelp('This is the help')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('howdy');
    }
}

