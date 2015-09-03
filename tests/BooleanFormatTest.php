<?php

/**
 * Created by PhpStorm.
 * User: enric
 * Date: 3/9/15
 * Time: 23:05
 */
class BooleanFormatTest extends PHPUnit_Framework_TestCase {
    function testIntegerToBooleanFormat() {
        $bool = new \Structure\BooleanS();

        $this->assertTrue($bool->format(1));
        $this->assertTrue($bool->format(-1));

        $this->assertFalse($bool->format(0));
    }

    function testNumericToBooleanFormat() {
        $bool = new \Structure\BooleanS();

        $this->assertTrue($bool->format("1"));
        $this->assertTrue($bool->format("-1"));

        $this->assertTrue($bool->format("1.5"));
        $this->assertTrue($bool->format("-1.5"));

        $this->assertFalse($bool->format("0"));
    }

    function testStringToBooleanFormat() {
        $bool = new \Structure\BooleanS();

        $this->assertTrue($bool->format("true"));

        $this->assertFalse($bool->format("false"));
    }
}
