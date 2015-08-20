<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Enric Florit
 * @since 0.5.0
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
