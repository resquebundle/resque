<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'resque:scheduledworker-start',
    description: 'Start a scheduled resque worker',
)]
class StartScheduledWorkerCommand extends Command
{
    public function __construct(
        private ParameterBagInterface $params)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
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
        $pidFile = $this->params->get('kernel.cache_dir').'/resque_scheduledworker.pid';
        if (file_exists($pidFile) && !$input->getOption('force')) {
            throw new \Exception('PID file exists - use --force to override');
        }

        if (file_exists($pidFile)) {
            unlink($pidFile);
        }

        $env = [
            'APP_INCLUDE' => $this->params->get('resque.app_include'),
            'VVERBOSE'    => 1,
            'RESQUE_PHP'  => $this->params->get('resque.vendor_dir').'/chrisboulton/php-resque/lib/Resque.php',
            'INTERVAL'    => $input->getOption('interval'),
        ];

        if (!file_exists($env['RESQUE_PHP'])) {
            $env['RESQUE_PHP'] =  $this->params->get('resque.vendor_dir').'/resque/php-resque/lib/Resque.php';
        }

        if (false !== getenv('APP_INCLUDE')) {
            $env['APP_INCLUDE'] = getenv('APP_INCLUDE');
        }

        $prefix =  $this->params->get('resque.prefix');

        if (!empty($prefix)) {
            $env['PREFIX'] =  $this->params->get('resque.prefix');
        }

        $redisHost     =  $this->params->get('resque.redis.host');
        $redisPort     =  $this->params->get('resque.redis.port');
        $redisDatabase =  $this->params->get('resque.redis.database');
        $redisPassword =  $this->params->get('resque.redis.password');

        if (null != $redisHost && null != $redisPort) {
            $env['REDIS_BACKEND'] = $redisHost.':'.$redisPort;
        }

        if (isset($redisDatabase)) {
            $env['REDIS_BACKEND_DB'] = $redisDatabase;
        }

        if (isset($redisPassword)) {
            $env['REDIS_BACKEND_PASSWORD'] = $redisPassword;
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
            $logFile =  $this->params->get(
                    'kernel.logs_dir'
                ).'/resque-scheduler_'.$this->params->get('kernel.environment').'.log';
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

        $process = Process::fromShellCommandline($workerCommand, null, $env, null, null);

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

        return Command::SUCCESS;
    }
}
