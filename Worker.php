<?php

namespace ResqueBundle\Resque;

/**
 * Class Worker
 * @package ResqueBundle\Resque
 */
class Worker
{
    /**
     * @var \Resque_Worker
     */
    protected $worker;

    /**
     * Worker constructor.
     * @param \Resque_Worker $worker
     */
    public function __construct(\Resque_Worker $worker)
    {
        $this->worker = $worker;
    }

    /**
     * @return bool
     */
    public function stop()
    {
        $parts = \explode(':', $this->getId());

        return \posix_kill($parts[1], SIGQUIT);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return (string)$this->worker;
    }

    /**
     * @return array
     */
    public function getQueues()
    {
        return \array_map(function($queue) {
            return new Queue($queue);
        }, $this->worker->queues());
    }

    /**
     * @return integer
     */
    public function getProcessedCount()
    {
        return $this->worker->getStat('processed');
    }

    /**
     * @return integer
     */
    public function getFailedCount()
    {
        return $this->worker->getStat('failed');
    }

    /**
     * @return \DateTime|null
     */
    public function getCurrentJobStart()
    {
        $job = $this->worker->job();

        if (!$job) {
            return NULL;
        }

        return new \DateTime($job['run_at']);
    }

    /**
     * @return null
     */
    public function getCurrentJob()
    {
        $job = $this->worker->job();

        if (!$job) {
            return NULL;
        }

        $job = new \Resque_Job($job['queue'], $job['payload']);

        return $job->getInstance();
    }

    /**
     * @return \Resque_Worker
     */
    public function getWorker()
    {
        return $this->worker;
    }
}
