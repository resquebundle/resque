<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Controller;

use ResqueBundle\Resque\Resque;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $this->getResque()->pruneDeadWorkers();

        return $this->render(
            '@Resque/Default/index.html.twig',
            [
                'resque' => $this->getResque(),
            ]
        );
    }

    /**
     * @return Resque
     */
    protected function getResque()
    {
        return $this->get('ResqueBundle\Resque\Resque');
    }

    /**
     * @param $queue
     * @param Request $request
     *
     * @return Response
     */
    public function showQueueAction($queue, Request $request)
    {
        list($start, $count, $showingAll) = $this->getShowParameters($request);

        $queue = $this->getResque()->getQueue($queue);
        $jobs  = $queue->getJobs($start, $count);

        if (!$showingAll) {
            $jobs = array_reverse($jobs);
        }

        return $this->render(
            '@Resque/Default/queue_show.html.twig',
            [
                'queue'      => $queue,
                'jobs'       => $jobs,
                'showingAll' => $showingAll,
            ]
        );
    }

    public function removeQueueAction($queue, Request $request)
    {
        $queue = $this->getResque()->getQueue($queue);
        $count = $queue->clear();
        $queue->remove();

        $this->addFlash('info', 'Remove '.$queue->getName().' queue and '.$count.' jobs.');

        return $this->redirectToRoute('ResqueBundle_homepage');
    }

    /**
     * Decide which parts of a job queue to show.
     *
     * @param Request $request
     *
     * @return array
     */
    private function getShowParameters(Request $request)
    {
        $showingAll = false;
        $start      = -100;
        $count      = -1;

        if ($request->query->has('all')) {
            $start      = 0;
            $count      = -1;
            $showingAll = true;
        }

        return [$start, $count, $showingAll];
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listFailedAction(Request $request)
    {
        list($start, $count, $showingAll) = $this->getShowParameters($request);

        $jobs = $this->getResque()->getFailedJobs($start, $count);

        if (!$showingAll) {
            $jobs = array_reverse($jobs);
        }

        return $this->render(
            '@Resque/Default/failed_list.html.twig',
            [
                'jobs'       => $jobs,
                'showingAll' => $showingAll,
            ]
        );
    }

    /**
     * @return Response
     */
    public function workersAction()
    {
        return $this->render(
            '@Resque/Default/workers.html.twig',
            [
                'resque' => $this->getResque(),
            ]
        );
    }

    /**
     * @return Response
     */
    public function listScheduledAction()
    {
        return $this->render(
            '@Resque/Default/scheduled_list.html.twig',
            [
                'timestamps' => $this->getResque()->getDelayedJobTimestamps(),
            ]
        );
    }

    /**
     * @param $timestamp
     *
     * @return Response
     */
    public function showTimestampAction($timestamp)
    {
        $jobs = [];

        // we don't want to enable the twig debug extension for this...
        foreach ($this->getResque()->getJobsForTimestamp($timestamp) as $job) {
            $jobs[] = print_r($job, true);
        }

        return $this->render(
            '@Resque/Default/scheduled_timestamp.html.twig',
            [
                'timestamp' => $timestamp,
                'jobs'      => $jobs,
            ]
        );
    }

    /**
     * @return RedirectResponse
     */
    public function retryFailedAction()
    {
        $count = $this->getResque()->retryFailedJobs();

        $this->addFlash('info', 'Retry '.$count.' failed jobs.');

        return $this->redirectToRoute('ResqueBundle_homepage');
    }

    /**
     * @return RedirectResponse
     */
    public function retryClearFailedAction()
    {
        $count = $this->getResque()->retryFailedJobs(true);

        $this->addFlash('info', 'Retry and clear '.$count.' failed jobs.');

        return $this->redirectToRoute('ResqueBundle_homepage');
    }

    /**
     * @return RedirectResponse
     */
    public function clearFailedAction()
    {
        $count = $this->getResque()->clearFailedJobs();

        $this->addFlash('info', 'Clear '.$count.' failed jobs.');

        return $this->redirectToRoute('ResqueBundle_homepage');
    }
}
