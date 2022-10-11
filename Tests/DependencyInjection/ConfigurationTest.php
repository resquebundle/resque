<?php
/*
 * @copyright  Copyright (C) 2019, 2022, 2021, 2022 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque\Tests\DependencyInjection;

use ResqueBundle\Resque\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class ConfigurationTest.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsConfigurationInterface()
    {
        $rc = new \ReflectionClass(Configuration::class);
        $this->assertTrue($rc->implementsInterface(ConfigurationInterface::class));
    }
}
