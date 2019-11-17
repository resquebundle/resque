<?php

namespace ResqueBundle\Resque;


/**
 * Class Job
 * @package ResqueBundle\Resque
 */
class Job extends ContainerAwareJob
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

    public function perform()
    {
        // boot container
        $container = $this->getContainer();

        // get the class from $args['resque.jobclass']
        $jobClass = $container->get($this->args['resque.jobclass']);

        // run the job
        $jobClass->run($this->args);
    }

    /**
     * Default function used to tear down, can be overloaded by individual Jobs
     * @todo test - I dont think this method is actually used?
     */
    public function tearDown()
    {

    }
}
