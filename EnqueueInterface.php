<?php
/*
 * @copyright  Copyright (C) 2019, 2022, 2021, 2022 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque;

/**
 * Interface EnqueueInterface.
 */
interface EnqueueInterface
{
    /**
     * @param Job  $job
     * @param bool $trackStatus
     *
     * @return mixed
     */
    public function enqueue(Job $job, $trackStatus = false);

    /**
     * @param Job  $job
     * @param bool $trackStatus
     *
     * @return mixed
     */
    public function enqueueOnce(Job $job, $trackStatus = false);

    /**
     * @param $at
     * @param Job $job
     *
     * @return mixed
     */
    public function enqueueAt($at, Job $job);

    /**
     * @param $in
     * @param Job $job
     *
     * @return mixed
     */
    public function enqueueIn($in, Job $job);
}
