<?php
/*
 * @copyright  Copyright (C) 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque;

use PHPUnit\Framework\TestCase;

class ResqueBundleTest extends TestCase
{
    /**
     * Test if the class is init correctly.
     */
    public function testResqueBundleinit()
    {
        $class = new ResqueBundle();
        $this->assertEquals('ResqueBundle\Resque\ResqueBundle', \get_class($class));
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Bundle\Bundle', $class);
    }
}
