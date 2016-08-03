<?php

namespace ResqueBundle\Resque;

/**
 * Interface EnqueueInterface
 */
interface EnqueueInterface
{
    /**
     * @param Job $job
     * @param bool $trackStatus
     * @return mixed
     */
    public function enqueue(Job $job, $trackStatus = FALSE);

    /**
     * @param Job $job
     * @param bool $trackStatus
     * @return mixed
     */
    public function enqueueOnce(Job $job, $trackStatus = FALSE);

    /**
     * @param $at
     * @param Job $job
     * @return mixed
     */
    public function enqueueAt($at, Job $job);

    /**
     * @param $in
     * @param Job $job
     * @return mixed
     */
    public function enqueueIn($in, Job $job);
}