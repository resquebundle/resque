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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'resque:worker-stop',
    description: 'Stop a resque worker',
)]
class StopWorkerCommand extends Command
{
    public function __construct(
        private Resque $resque)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('id', InputArgument::OPTIONAL, 'Worker id')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Should kill all workers');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            $workers = $this->resque->getWorkers();
        } else {
            $worker = $this->resque->getWorker($input->getArgument('id'));

            if (!$worker) {
                $availableWorkers = $this->resque->getWorkers();
                if (!empty($availableWorkers)) {
                    $output->writeln('<error>You need to give an existing worker.</error>');
                    $output->writeln('Running workers are:');
                    foreach ($this->resque->getWorkers() as $worker) {
                        $output->writeln($worker->getId());
                    }
                } else {
                    $output->writeln('<error>There are no running workers.</error>');
                }

                return Command::FAILURE;
            }

            $workers = [$worker];
        }

        if (!\count($workers)) {
            $output->writeln('<error>There are no running workers to stop.</error>');

            return Command::SUCCESS;
        }

        foreach ($workers as $worker) {
            if ($worker->stop()) {
                // The worker was successfully killed.
                $worker->getWorker()->unregisterWorker();
                $output->writeln(sprintf('Stopped %s', $worker->getId()));
            } else {
                $output->writeln(sprintf('<error>Failed to stop %s</error>', $worker->getId()));
            }
        }

        return Command::SUCCESS;
    }
}
