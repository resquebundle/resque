<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\ExampleJobs;

use ResqueBundle\Resque\Job;

class PingJob extends Job
{
    /**
     * @var string The queue name
     */
    public $queue = 'myqueue';

    public function run($args)
    {
        echo 'PONG!';
    }
}
