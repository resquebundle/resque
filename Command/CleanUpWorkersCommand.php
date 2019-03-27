<?php

namespace ResqueBundle\Resque\Command;

use ResqueBundle\Resque\Resque;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanUpWorkersCommand
 * @package ResqueBundle\Resque\Command
 */
class CleanUpWorkersCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('resque:cleanup:workers')
            ->setDescription('Unregisters all workers in Redis. Workers may need to be restarted.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resque = $this->getContainer()->get('ResqueBundle\Resque\Resque');

        if ($resque instanceof Resque) {
            $workers = $resque->getWorkers();

            foreach ($workers as $worker) {
                $output->writeln(sprintf('Unregistered Worker: %s', $worker->getId()));
                $worker->getWorker()->unregisterWorker();
            }
        }
    }
}
