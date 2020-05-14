<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class StartScheduledWorkerCommand.
 */
class StartScheduledWorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('resque:scheduledworker-start')
            ->setDescription('Start a scheduled resque worker')
            ->addOption('foreground', 'f', InputOption::VALUE_NONE, 'Should the worker run in foreground')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force creation of a new worker if the PID file exists')
            ->addOption('interval', 'i', InputOption::VALUE_REQUIRED, 'How often to check for new jobs across the queues', 5);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pidFile = $this->getContainer()->get('kernel')->getCacheDir().'/resque_scheduledworker.pid';
        if (file_exists($pidFile) && !$input->getOption('force')) {
            throw new \Exception('PID file exists - use --force to override');
        }

        if (file_exists($pidFile)) {
            unlink($pidFile);
        }

        $env = [
            'APP_INCLUDE' => $this->getContainer()->getParameter('resque.app_include'),
            'VVERBOSE'    => 1,
            'RESQUE_PHP'  => $this->getContainer()->getParameter('resque.vendor_dir').'/chrisboulton/php-resque/lib/Resque.php',
            'INTERVAL'    => $input->getOption('interval'),
        ];
        
        if (!file_exists($env['RESQUE_PHP'])){
            $env['RESQUE_PHP'] = $this->getContainer()->getParameter('resque.vendor_dir').'/resque/php-resque/lib/Resque.php';
        }

        if (false !== getenv('APP_INCLUDE')) {
            $env['APP_INCLUDE'] = getenv('APP_INCLUDE');
        }

        $prefix = $this->getContainer()->getParameter('resque.prefix');

        if (!empty($prefix)) {
            $env['PREFIX'] = $this->getContainer()->getParameter('resque.prefix');
        }

        $redisHost     = $this->getContainer()->getParameter('resque.redis.host');
        $redisPort     = $this->getContainer()->getParameter('resque.redis.port');
        $redisDatabase = $this->getContainer()->getParameter('resque.redis.database');

        if (null != $redisHost && null != $redisPort) {
            $env['REDIS_BACKEND'] = $redisHost.':'.$redisPort;
        }

        if (isset($redisDatabase)) {
            $env['REDIS_BACKEND_DB'] = $redisDatabase;
        }

        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $phpExecutable = PHP_BINARY;
        } else {
            $phpExecutable = PHP_BINDIR.'/php';
            if (\defined('PHP_WINDOWS_VERSION_BUILD')) {
                $phpExecutable = 'php';
            }
        }

        $workdirectory = __DIR__.'/../bin/';
        $workerCommand = $phpExecutable.' '.$workdirectory.'resque-scheduler';

        if (!$input->getOption('foreground')) {
            $logFile = $this->getContainer()->getParameter(
                    'kernel.logs_dir'
                ).'/resque-scheduler_'.$this->getContainer()->getParameter('kernel.environment').'.log';
            $workerCommand = 'nohup '.$workerCommand.' > '.$logFile.' 2>&1 & echo $!';
        }

        // In windows: When you pass an environment to CMD it replaces the old environment
        // That means we create a lot of problems with respect to user accounts and missing vars
        // this is a workaround where we add the vars to the existing environment.
        if (\defined('PHP_WINDOWS_VERSION_BUILD')) {
            foreach ($env as $key => $value) {
                putenv($key.'='.$value);
            }
            $env = null;
        }

        $process = new Process($workerCommand, null, $env, null, null);

        $output->writeln(sprintf('Starting worker <info>%s</info>', $process->getCommandLine()));

        if ($input->getOption('foreground')) {
            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });
        } // else we recompose and display the worker id
        else {
            $process->run();
            $pid = trim($process->getOutput());
            if (\function_exists('gethostname')) {
                $hostname = gethostname();
            } else {
                $hostname = php_uname('n');
            }
            $output->writeln(sprintf('<info>Worker started</info> %s:%s', $hostname, $pid));
            file_put_contents($pidFile, $pid);
        }
    }
}
