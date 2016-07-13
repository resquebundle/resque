<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 13/07/2016
 * Time: 3:37 PM
 */

namespace Mpclarkson\ResqueBundle\Tests\DependencyInjection;
use Mpclarkson\ResqueBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @covers \Mpclarkson\ResqueBundle\DependencyInjection\Configuration
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsConfigurationInterface()
    {
        $rc = new \ReflectionClass(Configuration::class);
        $this->assertTrue($rc->implementsInterface(ConfigurationInterface::class));
    }
}