<?php

namespace Mpclarkson\ResqueBundle;

/**
 * Class FailedJob
 * @package Mpclarkson\ResqueBundle
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
}
