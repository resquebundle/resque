<?php

namespace ResqueBundle\Resque;

/**
 * Class FailedJob
 * @package ResqueBundle\Resque
 */
class FailedJob
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data Contains the failed job data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getFailedAt()
    {
        return $this->data['failed_at'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->data['payload']['class'];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->data['payload']['id'];
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->data['queue'];
    }

    /**
     * @return string
     */
    public function getWorkerName()
    {
        return $this->data['worker'];
    }

    /**
     * @return string
     */
    public function getExceptionClass()
    {
        return $this->data['exception'];
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->data['error'];
    }

    /**
     * @return mixed
     */
    public function getBacktrace()
    {
        return $this->data['backtrace'];
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->data['payload']['args'];
    }

    /**
     * @return bool
     */
    public function hasRetryStrategy()
    {
        return
            array_key_exists('resque.retry_strategy', $this->data['payload']['args'][0]) &&
            count($this->data['payload']['args'][0]['resque.retry_strategy']) > 0;
    }

    /**
     * @return bool
     */
    public function isLastAttempt()
    {
        return
            array_key_exists('resque.retry_attempt', $this->data['payload']['args'][0]) &&
            count($this->data['payload']['args'][0]['resque.retry_strategy']) === $this->data['payload']['args'][0]['resque.retry_attempt'];
    }
}
