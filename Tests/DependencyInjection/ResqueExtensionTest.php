<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 13/07/2016
 * Time: 3:39 PM
 */

namespace Mpclarkson\ResqueBundle\Tests\DependencyInjection;


use Mpclarkson\ResqueBundle\DependencyInjection\ResqueExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $config = array('key' => 'foo');
        $loader->load(array($config), new ContainerBuilder());
    }
}