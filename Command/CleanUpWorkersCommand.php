<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Command;

use ResqueBundle\Resque\Resque;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanUpWorkersCommand.
 */
class CleanUpWorkersCommand extends Command
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
            ->setName('resque:cleanup:workers')
            ->setDescription('Unregisters all workers in Redis. Workers may need to be restarted.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->resque instanceof Resque) {
            $workers = $this->resque->getWorkers();

            foreach ($workers as $worker) {
                $output->writeln(sprintf('Unregistered Worker: %s', $worker->getId()));
                $worker->getWorker()->unregisterWorker();
            }
        }

        return 0;
    }
}
