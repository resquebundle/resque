<?php

namespace Mpclarkson\ResqueBundle\Tests\DependencyInjection;

use Mpclarkson\ResqueBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class ConfigurationTest
 * @package Mpclarkson\ResqueBundle\Tests\DependencyInjection
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testImplementsConfigurationInterface()
    {
        $rc = new \ReflectionClass(Configuration::class);
        $this->assertTrue($rc->implementsInterface(ConfigurationInterface::class));
    }
}