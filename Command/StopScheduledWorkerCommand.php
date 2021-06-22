<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class StopScheduledWorkerCommand.
 */
class StopScheduledWorkerCommand extends Command
{
    private $params;

    public function __construct(string $name = null, ParameterBagInterface $params)
    {
        $this->params = $params;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('resque:scheduledworker-stop')
            ->setDescription('Stop a resque scheduled worker');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pidFile = $this->params->get('kernel.cache_dir').'/resque_scheduledworker.pid';

        if (!file_exists($pidFile)) {
            $output->writeln('No PID file found');

            return 1;
        }

        $pid = file_get_contents($pidFile);

        $output->writeln('Killing process '.$pid);

        posix_kill($pid, SIGKILL);

        unlink($pidFile);

        return 0;
    }
}
