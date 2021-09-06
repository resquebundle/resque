<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use ResqueBundle\Resque\ExampleJobs\PingJob;
use ResqueBundle\Resque\Resque;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'resque:pingtest',
    description: 'Send a Ping as a test, and let a job reply with pong',
)]
class PingTestCommand extends Command
{
    public function __construct(
        private Resque $resque
    )
    {
        parent::__construct();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $job = new PingJob();
        $output->writeln($this->resque->enqueue($job, true));

        return Command::SUCCESS;
    }
}
