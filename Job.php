<?php

namespace Mpclarkson\ResqueBundle;

/**
 * Class Job
 * @package Mpclarkson\ResqueBundle
 */
abstract class Job
{
    /**
     * @var \Resque_Job
     */
    public $job;

    /**
     * @var string The queue name
     */
    public $queue = 'default';

    /**
     * @var array The job args
     */
    public $args = [];

    /**
     * @return string
     */
    public function getName()
    {
        return \get_class($this);
    }

    /**
     *
     */
    public function setUp()
    {

    }

    /**
     *
     */
    public function perform()
    {
        $this->run($this->args);
    }

    /**
     * @param $args
     * @return mixed
     */
    abstract public function run($args);

    /**
     *
     */
    public function tearDown()
    {

    }
}
