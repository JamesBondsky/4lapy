<?php

namespace FourPaws\Test\Tests;

use PHPUnit_Framework_Assert;

class DemoTest extends TestBase
{
    public function testSomethingEasy()
    {

        PHPUnit_Framework_Assert::assertEquals(1, 1, "good");
        PHPUnit_Framework_Assert::assertEquals(
            '/home/vagrant/project/web',
            $_SERVER['DOCUMENT_ROOT'],
            "check document root is set"
        );

    }
}
