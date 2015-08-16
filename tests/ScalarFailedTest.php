<?php

/**
 * Created by PhpStorm.
 * User: enric
 * Date: 16/8/15
 * Time: 17:17
 */

use Structure\Structure;
use Structure\ScalarS;
class ScalarFailedTest extends PHPUnit_Framework_TestCase {
    function testScalarFail() {
        Structure::clearLastFail();
        $this->assertEquals(null, Structure::getLastFail());

        $scalar = new ScalarS();
        foreach (array("hello world", 12, true, false, 1.3, "1.2") as $s) {
            $this->assertTrue($scalar->check($s));
            $this->assertEquals(null, Structure::getLastFail());
        }

        $this->assertFalse($scalar->check(array()));
        $this->assertEquals("array", Structure::getLastFail());

        $this->assertFalse($scalar->check(function() {}));
        $this->assertEquals("closure", Structure::getLastFail());

        $this->assertFalse($scalar->check(new Test()));
        $this->assertEquals("object", Structure::getLastFail());
    }
}

class Test {}
