<?php

namespace ResqueBundle\Resque;

/**
 * Class Job
 * @package ResqueBundle\Resque
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
     * Job constructor.
     * @param array $args The Job Arguments
     */
    public function __construct($args = [])
    {
        $this->args = $args;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return \get_class($this);
    }

    /**
     * Default function used to set up, can be overloaded by individual Jobs
     * @todo test - I dont think this method is actually used?
     */
    public function setUp()
    {

    }

    /**
     * @todo test - I dont think this method is actually used?
     */
    public function perform()
    {
        $this->run($this->args);
    }

    /**
     * This is the method that is called when the job is run
     *
     * @param $args
     * @return mixed
     */
    abstract public function run($args);

    /**
     * Default function used to tear down, can be overloaded by individual Jobs
     * @todo test - I dont think this method is actually used?
     */
    public function tearDown()
    {

    }
}
