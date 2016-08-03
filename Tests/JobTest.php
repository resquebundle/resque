<?php
/**
 * @author    Phil Taylor <phil@phil-taylor.com>
 * @source    https://github.com/PhilETaylor/
 * @copyright Copyright (C) 2016 Blue Flame IT Ltd. All rights reserved.
 */

namespace ResqueBundle\Resque;


class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testJobClass()
    {
        $stub = $this->getMockForAbstractClass('ResqueBundle\Resque\Job', [[1, 2, 3]]);

        // test init correctly
        $this->assertContains('Job', $stub->getName());
        $this->assertEquals('default', $stub->queue);
        $this->assertEquals([1, 2, 3], $stub->args);


        // test that perform calls run
        $stub
            ->expects($this->once())
            ->method('run');

        $stub->perform();

    }
}
