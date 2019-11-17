<?php

namespace ResqueBundle\Resque;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ContainerAwareJob
 * @package ResqueBundle\Resque
 */
abstract class ContainerAwareJob
{
    /**
     * @var KernelInterface
     */
    private $kernel = NULL;

    /**
     * @param array $kernelOptions
     */
    public function setKernelOptions(array $kernelOptions)
    {
        $this->args = \array_merge($this->args, $kernelOptions);
    }

    /**
     *
     */
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
        if ($this->kernel === NULL) {
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
        echo' <>>>>>>>>>>> '. __FILE__.':'.__LINE__."\n"; dump($this->args);
        $finder = new Finder();
        $finder->name('*Kernel.php')->depth(0)->in($this->args['kernel.root_dir']);
        $results = iterator_to_array($finder);
        $file = current($results);
        $class = $file->getBasename('.php');

        require_once $file;

        return new $class(
            isset($this->args['kernel.environment']) ? $this->args['kernel.environment'] : 'dev',
            isset($this->args['kernel.debug']) ? $this->args['kernel.debug'] : TRUE
        );
    }
}
