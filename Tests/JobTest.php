<?php
/*
 * @copyright  Copyright (C) 2019, 2022, 2021, 2022 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com> and others, see README.md
 * @see        https://github.com/resquebundle/resque
 * @license    MIT
 */

namespace ResqueBundle\Resque;

class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testJobClass()
    {
        $stub = $this->getMockForAbstractClass('ResqueBundle\Resque\Job', [[1, 2, 3]]);

        // test init correctly
        $this->assertContains('Job', $stub->getName());
        $this->assertInstanceOf('PHPUnit_Framework_MockObject_MockObject', $stub);
        $this->assertEquals('default', $stub->queue);
        $this->assertEquals([1, 2, 3], $stub->args);

        // test that perform calls run
        $stub
            ->expects($this->once())
            ->method('run');

        $stub->perform();
    }
}
