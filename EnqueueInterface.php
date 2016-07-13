<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 13/07/2016
 * Time: 2:39 PM
 */

namespace Mpclarkson\ResqueBundle;


/**
 * Interface EnqueueInterface
 * @package Mpclarkson\ResqueBundle
 */
interface EnqueueInterface
{

    public function enqueue(Job $job, $trackStatus = false);
    public function enqueueOnce(Job $job, $trackStatus = false);
    public function enqueueAt($at, Job $job);
    public function enqueueIn($in, Job $job);
}