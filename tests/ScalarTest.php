<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Enric Florit
 * @since 0.1.0
 * @date 13/7/15
 */

class ScalarTest extends PHPUnit_Framework_TestCase {
    public function testCheckType() {
        $scalar = new \Structure\ScalarS();

        $scalar->setData("Hello world");
        $this->assertTrue($scalar->check());

        $scalar->setData(array());
        $this->assertFalse($scalar->check());
    }

    public function testValueSet1() {
        $scalar = new \Structure\ScalarS();
        $scalar->setValueSet("hello", 1, 3.4, false);

        $this->assertTrue($scalar->check("hello"));
        $this->assertTrue($scalar->check(1));
        $this->assertTrue($scalar->check(3.4));
        $this->assertTrue($scalar->check(false));

        $this->assertFalse($scalar->check("world"));
        $this->assertFalse($scalar->check(2));
        $this->assertFalse($scalar->check(3.41));
        $this->assertFalse($scalar->check(true));
    }

    public function testValueSet2() {
        $scalar = new \Structure\ScalarS();
        $scalar->setValueSet("{hello, 1, 3.4, false}");

        $this->assertTrue($scalar->check("hello"));
        $this->assertTrue($scalar->check(1));
        $this->assertTrue($scalar->check(3.4));
        $this->assertTrue($scalar->check(false));

        $this->assertFalse($scalar->check("world"));
        $this->assertFalse($scalar->check(2));
        $this->assertFalse($scalar->check(3.41));
        $this->assertFalse($scalar->check(true));
    }

    /**
     * testing edge cases
     */
    public function testValueSet3() {
        $scalar = new \Structure\ScalarS();
        try {
            $scalar->setValueSet("{}");
        } catch (\Exception $e) {
            $this->fail("Unexpected \\Exception: '" . $e->getMessage() . "'");
        }
        $scalar->setValueSet("{,}");
        $this->assertEquals(array("", ""), $scalar->getValueSet());
    }

    /**
     * testing exceptions
     * @expectedException \Exception
     * @expectedExceptionMessage Expected character '}' at the end of value set string
     */
    public function testValueSet4() {
        $scalar = new \Structure\ScalarS();
        $scalar->setValueSet("{hello,1");
    }

    /**
     * testing value sets for specific types
     */
    public function testValueSet5() {
        $string = new \Structure\StringS();
        $string->setValueSet("{hello,world,foo,bar}");

        foreach (array("hello", "world", "foo", "bar") as $value) {
            $this->assertTrue($string->check($value));
        }

        $this->assertFalse($string->check("xyz"));
        $this->assertFalse($string->check("var"));

        $numeric = new \Structure\NumericS();
        $numeric->setValueSet("{3,4,6}"); // this couldn't be done with setRange("[3,6]")
        $this->assertTrue($numeric->check(3));
        $this->assertTrue($numeric->check(4));
        $this->assertFalse($numeric->check(5));
        $this->assertTrue($numeric->check(6));

        $this->assertFalse($numeric->check(3.9));
        $this->assertFalse($numeric->check(9.231));

        // it is kinda silly to use valueSets for boolean, but you can still do it
    }

    public function testValueSet6() {
        $numeric = new \Structure\NumericS();
        $this->assertFalse($numeric->setValueSet("{hello,3}"));
    }

    public function testValueSet7() {
        $scalar = new \Structure\ScalarS();
        $scalar->setValueSet("{hello\, world!, {scape\}}");

        $this->assertTrue($scalar->check("hello, world!"));
        $this->assertTrue($scalar->check("{scape}"));
    }
}
