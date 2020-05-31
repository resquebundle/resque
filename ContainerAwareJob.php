<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ContainerAwareJob.
 */
abstract class ContainerAwareJob
{
    /**
     * @var KernelInterface
     */
    private $kernel = null;

    /**
     * @param array $kernelOptions
     */
    public function setKernelOptions(array $kernelOptions)
    {
        $this->args = array_merge($this->args, $kernelOptions);
    }

    public function tearDown()
    {
        if ($this->kernel) {
            $this->kernel->shutdown();
        }
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (null === $this->kernel) {
            $this->kernel = $this->createKernel();
            $this->kernel->boot();
        }

        return $this->kernel->getContainer();
    }

    /**
     * @return KernelInterface
     */
    protected function createKernel()
    {
        $finder = new Finder();
        $finder->name('*Kernel.php')->depth('<=1')->in($this->args['kernel.project_dir'].'/src');
        $results = iterator_to_array($finder);
        $file    = current($results);
        $class   = $file->getBasename('.php');

        // Take into account any namespace
        preg_match('/namespace\s(.*)\;/', file_get_contents($file), $matches);
        if (2 == \count($matches)) {
            $namespace = '\\'.$matches[1].'\\';
            $class     = $namespace.$class;
        }

        require_once $file;

        return new $class(
            isset($this->args['kernel.environment']) ? $this->args['kernel.environment'] : 'dev',
            isset($this->args['kernel.debug']) ? $this->args['kernel.debug'] : true
        );
    }
}
