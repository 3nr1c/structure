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

class ArrayTest extends PHPUnit_Framework_TestCase {
    public function testArrayType() {
        $arr = new \Structure\ArrayS();
        $this->assertTrue($arr->check(array()));
        $this->assertFalse($arr->check("hello"));
        $this->assertFalse($arr->check(123));
        $this->assertFalse($arr->check(1.23));
        $this->assertFalse($arr->check(false));
    }
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

    public function testArraySeq2() {
        $format = array(
            "int",
            "string",
            array("foo" => "integer[1,3]")
        );

        $array = new \Structure\ArrayS();
        $array->setFormat($format);

        $correct = array(
            51,
            "hello",
            array(
                "foo" => 3
            )
        );

        $this->assertTrue($array->check($correct));
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

        $array->setFormat(array("foo" => "[]"));
        $this->assertTrue($array->check(array("foo" => array())));
        $this->assertFalse($array->check(array("foo" => 10)));
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

    public function testScalarArray() {
        $test = \Structure\Structure::ArrayS("scalar[]");
        $array = array(
            1,
            "hello",
            true,
            2.4
        );

        $this->assertTrue($test->check($array));

        $array[] = array();
        $this->assertFalse($test->check($array));
    }

    public function testGeneral1() {
        $arrayCheck = new \Structure\ArrayS();
        $arrayCheck->setFormat(array(
            "id" => "int",
            "rating" => "float[0,10]",
            "title" => array(
                "en" => "str",
                "es" => "str"
            ),
            "links" => "string[]",
            "subtitles" => "bool"
        ));

        $data = array(
            "id" => 1190080,
            "rating" => 5.8,
            "title" => array(
                "en" => "2012",
                "es" => "2012"
            ),
            "links" => array("http://www.imdb.com/title/tt1190080/?ref_=fn_al_tt_1"),
            "subtitles" => true
        );

        $this->assertTrue($arrayCheck->check($data));
    }

    public function testStrictCount1() {
        $array = new \Structure\ArrayS();
        $array->setFormat(array(
            "foo" => "string"
        ));

        $data = array(
            "foo" => "expected key",
            "bar" => "unexpected key",
            "xyz" => array(
                "something", 3, false, true, 3.2
            )
        );

        $this->assertFalse($array->check($data));
        $array->setCountStrict(false);
        $this->assertTrue($array->check($data));
    }

    public function testNull1() {
        $array = new \Structure\ArrayS();
        $array->setFormat(array(
            "foo" => "float"
        ));

        $this->assertFalse($array->check(array()));
    }

    public function testNull2() {
        $array = new \Structure\ArrayS();
        $array->setFormat(array(
            "foo" => "float"
        ));
        $array->setNull(true);

        $this->assertTrue($array->check(array()));
    }

    public function testValueSets1() {
        $format = array(
            "string{a,b,c}",
            "integer{2, 4, 6}"
        );

        $array = \Structure\Structure::ArrayS($format);

        $correct = array("", 2);

        foreach (array("a", "b", "c") as $str) {
            $correct[0] = $str;
            foreach (array(2,4,6) as $int) {
                $correct[1] = $int;
                $this->assertTrue($array->check($correct));
            }
        }

        $incorrect1 = array("d", 2);
        $this->assertFalse($array->check($incorrect1));

        $incorrect2 = array(false, 2);
        $this->assertFalse($array->check($incorrect2));

        $incorrect3 = array("a", 3);
        $this->assertFalse($array->check($incorrect3));
    }

    public function testValueSets2() {
        $format = array(
            "foo" => "scalar{true,1}",
            "bar" => "float{10,20,30,40,50,60,70,80,90}"
        );
        $array = \Structure\Structure::ArrayS($format);

        $correct = array();

        foreach (array(true, 1) as $foo) {
            $correct["foo"] = $foo;
            foreach (array(10,20,30,40,50,60,70,80,90) as $bar) {
                $correct["bar"] = $bar;
                $valid = $array->check($correct);
                $this->assertTrue($valid);
            }
        }
    }

    public function testInfinities() {
        $format = array(
            "foo" => "numeric(0, +inf)"
        );
        $array = \Structure\Structure::ArrayS($format);
        $this->assertTrue($array->check(array("foo" => "3")));
        $this->assertFalse($array->check(array("foo" => "-1")));
        $this->assertFalse($array->check(array("foo" => "0")));
    }

    public function testMinimumNumber() {
        $array = \Structure\Structure::ArrayS("str[+]");

        $this->assertFalse($array->check(array()));
        $this->assertTrue($array->check(array("hello world!")));
        $this->assertFalse($array->check(array("hello", 3)));

        $array->setFormat("int[3+]");

        $this->assertFalse($array->check(array()));
        $this->assertFalse($array->check(array(1)));
        $this->assertFalse($array->check(array(1, 2)));
        $this->assertTrue($array->check(array(1, 2, 3)));
        $this->assertTrue($array->check(array(1, 2, 3, 4)));
        $this->assertFalse($array->check(array(1, 2, 3, true)));

        $array->setFormat("scalar[*]");

        $this->assertTrue($array->check(array()));
        $this->assertTrue($array->check(array(true)));
        $this->assertTrue($array->check(array(true, 1)));
        $this->assertTrue($array->check(array(true, 1, "string")));
    }
}
