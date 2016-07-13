<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 25/06/2014
 * Time: 9:30 AM
 */

namespace Mpclarkson\ResqueBundle\Command;

use Mpclarkson\ResqueBundle\Resque;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUpWorkersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('resque:cleanup:workers')
            ->setDescription('Unregisters all workers in Redis. Workers may need to be restarted.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resque = $this->getContainer()->get('resque');

        if ($resque instanceof Resque) {
            $workers = $resque->getWorkers();

            foreach ($workers as $worker) {
                $output->writeln(sprintf('Unregistered Worker: %s', $worker->getId()));
                $worker->getWorker()->unregisterWorker();
            }
        }

        $output->writeln('*** Finished ***');
    }
}
