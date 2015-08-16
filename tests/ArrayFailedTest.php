<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Enric Florit
 * @since 0.5.0
 */

use Structure\ArrayS;

class ArrayFailedTest extends PHPUnit_Framework_TestCase {
    function testFailedLinearArray1() {
        $array = new ArrayS();
        $array->setFormat(array("integer", "string", "float", "boolean"));

        $correct = array(214, "test", 123.45, false);
        $this->assertTrue($array->check($correct, $failed));
        $this->assertEmpty($failed);


        $incorrect1 = array("test", 214, false, 123.45);
        $this->assertFalse($array->check($incorrect1, $failed));
        $this->assertEquals(array("string", "integer", "boolean", "float"), $failed);

        $incorrect2 = array("214", "test", 123.45, false);
        $this->assertFalse($array->check($incorrect2, $failed));
        $this->assertEquals(array(0 => "string"), $failed);

        $incorrect3 = array(214, 213, 123.45, false);
        $this->assertFalse($array->check($incorrect3, $failed));
        $this->assertEquals(array(1 => "integer"), $failed);

        $incorrect4 = array(214, "test", true, false);
        $this->assertFalse($array->check($incorrect4, $failed));
        $this->assertEquals(array(2 => "boolean"), $failed);
    }

    function testFailedLinearArray2() {
        $array = new ArrayS();
        $array->setFormat("(string|null)[5]");

        $correct = array("abc", "cde", "efg", "ghi");
        $this->assertTrue($array->check($correct, $failed));
        $this->assertEmpty($failed);

        $incorrect = array(123, "cde", array(), "ghi");
        $this->assertFalse($array->check($incorrect, $failed));
        $this->assertEquals(array(0 => "integer", 2 => "array"), $failed);
    }

    function testFailedLinearArray3() {
        $array = new ArrayS();
        $array->setFormat("scalar[]");

        $correct = array("abc", 30);
        $this->assertTrue($array->check($correct, $failed));
        $this->assertEquals(array(), $failed);

        $incorrect = array("abc", array(), new SplStack());
        $this->assertFalse($array->check($incorrect, $failed));
        $this->assertEquals(array(1 => "array", 2 => "object"), $failed);
    }

    function testFailedLinearValueSet() {
        $array = new ArrayS();
        $array->setFormat("scalar{1, true, 1.0}[+]");

        $correct = array(1, 1., 1, true);
        $this->assertTrue($array->check($correct, $failed));
        $this->assertEmpty($failed);

        $incorrect1 = array(1, false, true);
        $this->assertFalse($array->check($incorrect1, $failed));
        $this->assertEquals(array(1 => "scalar:value"), $failed);

        $incorrect2 = array((object)array(), function() {});
        $this->assertFalse($array->check($incorrect2, $failed));
        $this->assertEquals(array("object", "closure"), $failed);
    }

    function testFailedRange() {
        $array = new ArrayS();
        $array->setFormat(array(
            "n" => "numeric(-5, 6)[]",
            "i" => "integer[2,30][]",
            "f" => "float[-10,-5)"
        ));

        $correct = array(
            "n" => array("-1", 0.5, 5),
            "i" => range(2, 30, 1),
            "f" => -10
        );

        $this->assertTrue($array->check($correct, $failed));
        $this->assertEquals(array(), $failed);

        $incorrect1 = array(
            "n" => array(0.5, "a", 5),
            "i" => range(2, 30, 1),
            "f" => -10
        );
        $this->assertFalse($array->check($incorrect1, $failed));
        $this->assertEquals(array(
            "n" => array(1 => "string")
        ), $failed);

        $incorrect2 = array(
            "n" => array("-1", 0.5, 5),
            "i" => array(2, 3, 4, 5, /*... ,*/ 29, 30, 31),
            "f" => -10
        );
        $this->assertFalse($array->check($incorrect2, $failed));
        $this->assertEquals(array(
            "i" => array(6 => "integer:range")
        ), $failed);

        $incorrect3 = array(
            "n" => array("-1", 0.5, 5),
            "i" => range(2, 30, 1),
            "f" => false
        );
        $this->assertFalse($array->check($incorrect3, $failed));
        $this->assertEquals(array(
            "f" => "boolean"
        ), $failed);
    }
}
