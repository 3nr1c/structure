<?php
/**
 * Created by PhpStorm.
 * User: enric
 * Date: 13/7/15
 * Time: 17:20
 */

class ArrayTest extends PHPUnit_Framework_TestCase {
    public function testArraySeq1() {
        $format = array("int", "float", "string");
        $array = new \Structure\ArrayS();
        $array->setFormat($format);

        $correct = array(1, 3.4, "hello world");
        $incorrect1 = array(1);
        $incorrect2 = array("a", "b", "c");
        $incorrect3 = array(1, 3.4, 2);
        $incorrect4 = "foo";
        $incorrect5 = array("foo" => "bar");

        $this->assertTrue($array->check($correct));
        $this->assertFalse($array->check($incorrect1));
        $this->assertFalse($array->check($incorrect2));
        $this->assertFalse($array->check($incorrect3));
        $this->assertFalse($array->check($incorrect4));
        $this->assertFalse($array->check($incorrect5));
    }

    public function testArrayAssoc1() {
        $format = array(
            "foo" => "integer",
            "bar" => "array",
            "abc" => "string"
        );

        $array = new \Structure\ArrayS();
        $array->setFormat($format);

        $correct = array(
            "foo" => 1234,
            "bar" => array("nothing", "to", "be", "checked"),
            "abc" => "test"
        );
        $incorrect1 = array(
            "foo" => "var",
            "bar" => array(),
            "abc" => "test"
        );
        $incorrect2 = array(1, 2, 3);
        $incorrect3 = array(
            "fo0" => 1234,
            "bar" => array(),
            "abc" => "test"
        );

        $this->assertTrue($array->check($correct));
        $this->assertFalse($array->check($incorrect1));
        $this->assertFalse($array->check($incorrect2));
        $this->assertFalse($array->check($incorrect3));
    }

    public function testArrayAssocRecursive() {
        $format = array(
            "foo" => "numeric",
            "bar" => array(
                "string",
                "integer"
            ),
            "a" => array(
                "xyz" => array("int", "int"),
                "asdf" => "bool"
            )
        );

        $array = new \Structure\ArrayS();
        $array->setFormat($format);

        $correct = array(
            "foo" => "23.2",
            "bar" => array(
                "hello world!",
                152
            ),
            "a" => array(
                "xyz" => array(1, 2),
                "asdf" => false
            )
        );

        $this->assertTrue($array->check($correct));
    }

    public function testArraySimple1() {
        $array = new \Structure\ArrayS();
        $array->setFormat("int[]");

        $this->assertTrue($array->check(array(1, 2, 3, 4, 5, 6)));
        $this->assertFalse($array->check(array(1, 2., 3, 4, 5, 6)));

        $array->setFormat("int[3]");

        $this->assertTrue($array->check(array(1, 2, 3)));
        $this->assertFalse($array->check(array(1, 2, 6, 4)));
        $this->assertFalse($array->check(array(1, 2.6, 4)));
    }

    public function testArraySimple2() {;
        $format = array(
            "foo" => "numeric",
            "bar" => array(
                "string",
                "integer[3,5)"
            ),
            "a" => "int[]"
        );

        $array = new \Structure\ArrayS();
        $array->setFormat($format);

        $correct = array(
            "foo" => 3.4,
            "bar" => array(
                "test",
                4
            ),
            "a" => array(1, 3, 4)
        );

        $incorrect1 = array(
            "foo" => 3.4,
            "bar" => array(
                "test",
                5//it must be STRICTLY less than 5
            ),
            "a" => array(1, 3, 4)
        );

        $incorrect2 = array(
            "foo" => 3.4,
            "bar" => array(
                "test",
                4
            ),
            "a" => array(1., 3, 4)
        );

        $this->assertTrue($array->check($correct));
        $this->assertFalse($array->check($incorrect1));
        $this->assertFalse($array->check($incorrect2));
    }

    public function testArraySimple3() {
        $array = new \Structure\ArrayS();
        $array->setFormat("[]");
        $this->assertTrue($array->check(array()));
        $this->assertTrue($array->check(array(1, "a", 13.2)));
    }
}
