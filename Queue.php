<?php
/*
 * @copyright  Copyright (C) 2019, 2022, 2021, 2022 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque;

/**
 * Class Queue.
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
     *
     * @return array
     */
    public function getJobs($start = 0, $stop = -1)
    {
        $jobs = \Resque::redis()->lrange('queue:' . $this->name, $start, $stop);

        $result = [];
        foreach ($jobs as $job) {
            $job      = new \Resque_Job($this->name, json_decode($job, true));
            $result[] = $job->getInstance();
        }

        return $result;
    }

    public function remove()
    {
        \Resque::redis()->srem('queues', $this->name);
    }

    /**
     * @return int
     */
    public function clear()
    {
        $length = \Resque::redis()->llen('queue:' . $this->name);
        \Resque::redis()->del('queue:' . $this->name);

        return $length;
    }
}
