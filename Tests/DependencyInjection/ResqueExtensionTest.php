<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Tests\DependencyInjection;

use ResqueBundle\Resque\DependencyInjection\ResqueExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ResqueExtensionTest.
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
