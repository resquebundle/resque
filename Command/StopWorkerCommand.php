<?php
/*
 * @copyright  Copyright (C) 2019, 2022, 2021, 2022 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Command;

use ResqueBundle\Resque\Resque;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StopWorkerCommand.
 */
class StopWorkerCommand extends Command
{
    private $resque;

    public function __construct(string $name = null, Resque $resque)
    {
        $this->resque = $resque;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('resque:worker-stop')
            ->setDescription('Stop a resque worker')
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

                return 1;
            }

            $workers = [$worker];
        }

        if (!\count($workers)) {
            $output->writeln('<error>There are no running workers to stop.</error>');

            return 0;
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

        return 0;
    }
}
