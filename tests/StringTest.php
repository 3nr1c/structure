<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Enric Florit
 * @since 0.5.0
 * @date 13/7/15
 */


use Structure\StringS;

class StringTest extends PHPUnit_Framework_TestCase {
    function testType() {
        $string = new StringS();

        $this->assertTrue($string->check("test", $fail));
        $this->assertEmpty($fail);

        $this->assertFalse($string->check(true, $fail));
        $this->assertEquals("boolean", $fail);
        $this->assertFalse($string->check(false, $fail));
        $this->assertEquals("boolean", $fail);
        $this->assertFalse($string->check(null, $fail));
        $this->assertEquals("null", $fail);
        $this->assertFalse($string->check(123, $fail));
        $this->assertEquals("integer", $fail);
        $this->assertFalse($string->check(123.4, $fail));
        $this->assertEquals("float", $fail);
    }

    function testLength1() {
        $string = new StringS();

        $string->setLength(5);
        $this->assertTrue($string->check("abcde"));
        $this->assertFalse($string->check("abcd"));

        $string->setLength("(5..)");
        $this->assertTrue($string->check("abcde"));
        $this->assertFalse($string->check("abcd"));

        $string->setLength(0, 5);
        $this->assertTrue($string->check("abcde"));
        $this->assertFalse($string->check("abcdef"));

        $string->setLength("(..5)");
        $this->assertTrue($string->check("abcde"));
        $this->assertFalse($string->check("abcdef"));

    }

    function testLength2() {
        $string = new StringS();

        $n = 0;
        do {
            if ($n == 0) $string->setLength(3, 5);
            else if ($n == 1) $string->setLength("(3..5)");

            $test = "";
            for ($i = 0; $i <= 7; $i++, $test .= 'x') {

                if ($i < 3 || $i > 5) {
                    $this->assertFalse($string->check($test, $fail), $i);
                    $this->assertEquals("string:length", $fail);
                } else {
                    $this->assertTrue($string->check($test, $fail));
                    $this->assertEmpty($fail);
                }
            }
        } while ($n++ < 1);
    }

    function testStringInArray() {
        $array = new \Structure\ArrayS();
        $array->setFormat(array("string(2..5)", "string(2..)", "string(..5)", "string(2..2)"));

        $correct = array("abc", "abcdefg", "abcde", "ab");

        $this->assertTrue($array->check($correct, $fail));
        $this->assertEmpty($fail);

        $incorrect = array("abcdef", "", "abcdefg", "abc");

        $this->assertFalse($array->check($incorrect, $fail));
        $this->assertEquals(array_fill(0, 4, "string:length"), $fail);
    }

    function testStringArrayCompact() {
        $array = new \Structure\ArrayS();
        $array->setFormat("string(1..)[+]");

        $correct1 = array("a");
        $correct2 = array("a", "abc");
        $this->assertTrue($array->check($correct1));
        $this->assertTrue($array->check($correct2));

        $incorrect1 = array();
        $incorrect2 = array("");
        $this->assertFalse($array->check($incorrect1));
        $this->assertFalse($array->check($incorrect2));
    }
}
