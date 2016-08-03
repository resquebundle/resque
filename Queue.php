<?php

namespace ResqueBundle\Resque;

/**
 * Class Queue
 * @package ResqueBundle\Resque
 */
class Queue
{
    /**
     * @var string The queue name
     */
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return \Resque::size($this->name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $start
     * @param int $stop
     * @return array
     */
    public function getJobs($start = 0, $stop = -1)
    {
        $jobs = \Resque::redis()->lrange('queue:' . $this->name, $start, $stop);

        $result = [];
        foreach ($jobs as $job) {
            $job = new \Resque_Job($this->name, \json_decode($job, TRUE));
            $result[] = $job->getInstance();
        }

        return $result;
    }
}
