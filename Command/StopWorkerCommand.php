<?php

namespace ResqueBundle\Resque\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StopWorkerCommand
 * @package ResqueBundle\Resque\Command
 */
class StopWorkerCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('resque:worker-stop')
            ->setDescription('Stop a resque worker')
            ->addArgument('id', InputArgument::OPTIONAL, 'Worker id')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Should kill all workers');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resque = $this->getContainer()->get('ResqueBundle\Resque\Resque');

        if ($input->getOption('all')) {
            $workers = $resque->getWorkers();
        } else {
            $worker = $resque->getWorker($input->getArgument('id'));

            if (!$worker) {
                $availableWorkers = $resque->getWorkers();
                if (!empty($availableWorkers)) {
                    $output->writeln('<error>You need to give an existing worker.</error>');
                    $output->writeln('Running workers are:');
                    foreach ($resque->getWorkers() as $worker) {
                        $output->writeln($worker->getId());
                    }
                } else {
                    $output->writeln('<error>There are no running workers.</error>');
                }

                return 1;
            }

            $workers = [$worker];
        }

        if (!count($workers)) {
            $output->writeln('<error>There are no running workers to stop.</error>');

            return 0;
        }

        foreach ($workers as $worker) {
            if ($worker->stop()) {
                // The worker was successfully killed.
                $worker->getWorker()->unregisterWorker();
                $output->writeln(\sprintf('Stopped %s', $worker->getId()));
            }
            else {
                $output->writeln(\sprintf('<error>Failed to stop %s</error>', $worker->getId()));
            }
        }

        return 0;
    }
}
