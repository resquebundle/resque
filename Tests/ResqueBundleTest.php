<?php
/**
 * @author    Phil Taylor <phil@phil-taylor.com>
 * @source    https://github.com/PhilETaylor/
 * @copyright Copyright (C) 2016 Blue Flame IT Ltd. All rights reserved.
 */

namespace ResqueBundle\Resque;

use PHPUnit\Framework\TestCase;


class ResqueBundleTest extends TestCase
{

    /**
     * Test if the class is init correctly
     */
    public function testResqueBundleinit()
    {
        $class = new ResqueBundle();
        $this->assertEquals('ResqueBundle\Resque\ResqueBundle', get_class($class));
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Bundle\Bundle', $class);
    }
}
