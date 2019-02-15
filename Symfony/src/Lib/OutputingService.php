<?php
declare(strict_types=1);

namespace App\Lib;

use Symfony\Component\Console\Output\OutputInterface;

abstract class OutputingService
{
    protected $output;

    public function setOutput(OutputInterface $output = null): void
    {
        $this->output = $output;
    }

    protected function writeln(string $ln): void
    {
        if ($this->output) {
            $this->output->writeln($ln);
        }
    }
}
