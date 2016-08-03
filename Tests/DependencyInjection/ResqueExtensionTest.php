<?php

namespace Mpclarkson\ResqueBundle\Tests\DependencyInjection;


use Mpclarkson\ResqueBundle\DependencyInjection\ResqueExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ResqueExtensionTest
 * @package Mpclarkson\ResqueBundle\Tests\DependencyInjection
 */
class ResqueExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testLoadExtensionThrowsExceptionIfNotConfigured()
    {
        $loader = new ResqueExtension();
        $config = ['key' => 'foo'];
        $loader->load([$config], new ContainerBuilder());
    }
}