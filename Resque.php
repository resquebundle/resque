<?php

namespace ResqueBundle\Resque;

use Psr\Log\NullLogger;

/**
 * Class Resque
 * @package ResqueBundle\Resque
 */
class Resque implements EnqueueInterface
{
    /**
     * @var array
     */
    private $kernelOptions;

    /**
     * @var array
     */
    private $redisConfiguration;

    /**
     * @var array
     */
    private $globalRetryStrategy = [];

    /**
     * @var array
     */
    private $jobRetryStrategy = [];

    /**
     * Resque constructor.
     * @param array $kernelOptions
     */
    public function __construct(array $kernelOptions)
    {
        $this->kernelOptions = $kernelOptions;
    }

    /**
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        \Resque_Redis::prefix($prefix);
    }

    /**
     * @param $strategy
     */
    public function setGlobalRetryStrategy($strategy)
    {
        $this->globalRetryStrategy = $strategy;
    }

    /**
     * @param $strategy
     */
    public function setJobRetryStrategy($strategy)
    {
        $this->jobRetryStrategy = $strategy;
    }

    /**
     * @return array
     */
    public function getRedisConfiguration()
    {
        return $this->redisConfiguration;
    }

    /**
     * @param $host
     * @param $port
     * @param $database
     */
    public function setRedisConfiguration($host, $port, $database)
    {
        $this->redisConfiguration = [
            'host'     => $host,
            'port'     => $port,
            'database' => $database,
        ];
        $host = substr($host, 0, 1) == '/' ? $host : $host . ':' . $port;

        \Resque::setBackend($host, $database);
    }

    /**
     * @param Job $job
     * @param bool $trackStatus
     * @return null|\Resque_Job_Status
     */
    public function enqueueOnce(Job $job, $trackStatus = FALSE)
    {
        $queue = new Queue($job->queue);
        $jobs = $queue->getJobs();

        foreach ($jobs AS $j) {
            if ($j->job->payload['class'] == get_class(Job::class)) {

                // add the kernel options
                if ($job instanceof Job) {
                    $job->setKernelOptions($this->kernelOptions);
                }

                // add the retry strategy
                $this->attachRetryStrategy($job);
                $this->wrap($job);

                // flatten recursive arrays
                $existingJob = \json_encode($j->args);
                $newJob = \json_encode($job->args);

                // Now we can compare the two strings
                if ($existingJob === $newJob){
                    return ($trackStatus) ? $j->job->payload['id'] : NULL;
                }
            }
        }

        return $this->enqueue($job, $trackStatus);
    }

    /**
     * @param Job $job
     * @param bool $trackStatus
     * @return null|\Resque_Job_Status
     */
    public function enqueue(Job $job, $trackStatus = FALSE)
    {
        if ($job instanceof Job) {
            $job->setKernelOptions($this->kernelOptions);
        }

        $this->attachRetryStrategy($job);
        $this->wrap($job);

        $result = \Resque::enqueue($job->queue, Job::class, $job->args, $trackStatus);

        if ($trackStatus && $result !== FALSE) {
            return new \Resque_Job_Status($result);
        }

        return NULL;
    }

    /**
     * @param Job $job
     */
    protected function wrap($job)
    {
        $job->args['resque.jobclass'] = get_class($job);
    }

    /**
     * Attach any applicable retry strategy to the job.
     *
     * @param Job $job
     */
    protected function attachRetryStrategy($job)
    {
        $class = get_class($job);

        if (isset($this->jobRetryStrategy[$class])) {
            if (count($this->jobRetryStrategy[$class])) {
                $job->args['resque.retry_strategy'] = $this->jobRetryStrategy[$class];
            }
        } elseif (count($this->globalRetryStrategy)) {
            $job->args['resque.retry_strategy'] = $this->globalRetryStrategy;
        }
    }

    /**
     * @param $at
     * @param Job $job
     * @return null
     */
    public function enqueueAt($at, Job $job)
    {
        if ($job instanceof Job) {
            $job->setKernelOptions($this->kernelOptions);
        }

        $this->attachRetryStrategy($job);
        $this->wrap($job);

        \ResqueScheduler::enqueueAt($at, $job->queue, Job::class, $job->args);

        return NULL;
    }

    /**
     * @param $in
     * @param Job $job
     * @return null
     */
    public function enqueueIn($in, Job $job)
    {
        if ($job instanceof Job) {
            $job->setKernelOptions($this->kernelOptions);
        }

        $this->attachRetryStrategy($job);
        $this->wrap($job);

        \ResqueScheduler::enqueueIn($in, $job->queue, Job::class, $job->args);

        return NULL;
    }

    /**
     * @param Job $job
     * @return mixed
     */
    public function removedDelayed(Job $job)
    {
        if ($job instanceof Job) {
            $job->setKernelOptions($this->kernelOptions);
        }

        $this->attachRetryStrategy($job);
        $this->wrap($job);

        return \ResqueScheduler::removeDelayed($job->queue, Job::class, $job->args);
    }

    /**
     * @param $at
     * @param Job $job
     * @return mixed
     */
    public function removeFromTimestamp($at, Job $job)
    {
        if ($job instanceof Job) {
            $job->setKernelOptions($this->kernelOptions);
        }

        $this->attachRetryStrategy($job);
        $this->wrap($job);

        return \ResqueScheduler::removeDelayedJobFromTimestamp($at, $job->queue, Job::class, $job->args);
    }

    /**
     * @return array
     */
    public function getQueues()
    {
        return \array_map(function($queue) {
            return new Queue($queue);
        }, \Resque::queues());
    }

    /**
     * @param $queue
     * @return Queue
     */
    public function getQueue($queue)
    {
        return new Queue($queue);
    }

    /**
     * @return Worker[]
     */
    public function getWorkers()
    {
        return \array_map(function($worker) {
            return new Worker($worker);
        }, \Resque_Worker::all());
    }

    /**
     * @return Worker[]
     */
    public function getRunningWorkers()
    {
        return array_filter($this->getWorkers(), function (Worker $worker) {
            return $worker->getCurrentJob() !== null;
        });
    }

    /**
     * @param $id
     * @return Worker|null
     */
    public function getWorker($id)
    {
        $worker = \Resque_Worker::find($id);

        if (!$worker) {
            return NULL;
        }

        return new Worker($worker);
    }

    /**
     * @return int
     */
    public function getNumberOfWorkers()
    {
        return \Resque::redis()->scard('workers');
    }

    /**
     * @return int
     */
    public function getNumberOfWorkingWorkers()
    {
        $count = 0;
        foreach ($this->getWorkers() as $worker) {
            if ($worker->getCurrentJob() !== null) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @todo - Clean this up, for now, prune dead workers, just in case
     */
    public function pruneDeadWorkers()
    {
        $worker = new \Resque_Worker('temp');
        $worker->setLogger(new NullLogger());
        $worker->pruneDeadWorkers();
    }

    /**
     * @return array|mixed
     */
    public function getFirstDelayedJobTimestamp()
    {
        $timestamps = $this->getDelayedJobTimestamps();
        if (count($timestamps) > 0) {
            return $timestamps[0];
        }

        return [NULL, 0];
    }

    /**
     * @return array
     */
    public function getDelayedJobTimestamps()
    {
        $timestamps = \Resque::redis()->zrange('delayed_queue_schedule', 0, -1);

        //TODO: find a more efficient way to do this
        $out = [];
        foreach ($timestamps as $timestamp) {
            $out[] = [$timestamp, \Resque::redis()->llen('delayed:' . $timestamp)];
        }

        return $out;
    }

    /**
     * @return mixed
     */
    public function getNumberOfDelayedJobs()
    {
        return \ResqueScheduler::getDelayedQueueScheduleSize();
    }

    /**
     * @param $timestamp
     * @return array
     */
    public function getJobsForTimestamp($timestamp)
    {
        $jobs = \Resque::redis()->lrange('delayed:' . $timestamp, 0, -1);
        $out = [];
        foreach ($jobs as $job) {
            $out[] = json_decode($job, TRUE);
        }

        return $out;
    }

    /**
     * @param $queue
     * @return int
     */
    public function clearQueue($queue)
    {
        return $this->getQueue($queue)->clear();
    }

    /**
     * @param int $start
     * @param int $count
     * @return array
     */
    public function getFailedJobs($start = -100, $count = 100)
    {
        $jobs = \Resque::redis()->lrange('failed', $start, $count);

        $result = [];

        foreach ($jobs as $job) {
            $result[] = new FailedJob(json_decode($job, TRUE));
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getNumberOfFailedJobs()
    {
        return \Resque::redis()->llen('failed');
    }

    /**
     * @param bool $clear
     *
     * @return int
     */
    public function retryFailedJobs($clear = false)
    {
        $jobs = \Resque::redis()->lrange('failed', 0, -1);
        if ($clear) {
            $this->clearFailedJobs();
        }
        foreach ($jobs as $job) {
            $failedJob = new FailedJob(json_decode($job, true));
            \Resque::enqueue($failedJob->getQueueName(), $failedJob->getName(), $failedJob->getArgs()[0]);
        }
        return count($jobs);
    }

    /**
     * @return int
     */
    public function clearFailedJobs()
    {
        $length = \Resque::redis()->llen('failed');
        if ($length > 0) {
            \Resque::redis()->del('failed');
        }

        return $length;
    }
}
