<?php

namespace ResqueBundle\Resque\Tests\DependencyInjection;

use ResqueBundle\Resque\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class ConfigurationTest
 * @package ResqueBundle\Resque\Tests\DependencyInjection
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