<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use ResqueBundle\Resque\Resque;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'resque:clear-queue',
    description: 'Clear a resque queue',
)]
class ClearQueueCommand extends Command
{
    public function __construct(
        private Resque $resque)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('queue', InputArgument::REQUIRED, 'Queue name');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getArgument('queue');
        $count = $this->resque->clearQueue($queue);

        $output->writeln('Cleared queue '.$queue.' - removed '.$count.' entries');

        return Command::SUCCESS;
    }
}
