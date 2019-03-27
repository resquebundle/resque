<?php

namespace ResqueBundle\Resque\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearQueueCommand
 * @package ResqueBundle\Resque\Command
 */
class ClearQueueCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('resque:clear-queue')
            ->setDescription('Clear a resque queue')
            ->addArgument('queue', InputArgument::REQUIRED, 'Queue name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resque = $this->getContainer()->get('ResqueBundle\Resque\Resque');

        $queue = $input->getArgument('queue');
        $count = $resque->clearQueue($queue);

        $output->writeln('Cleared queue ' . $queue . ' - removed ' . $count . ' entries');

        return 0;
    }
}
