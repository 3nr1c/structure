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

    public function testRangeNumericArray() {
        $array = \Structure\Structure::ArrayS("integer[-5, 5][]");

        $test1 = array(-5, 0, -3, 4, 5);
        $test2 = array();

        $test3 = array(-6, 0, -3, 4, 5);
        $test4 = array(10);
        $test5 = array(0, 1, 2, 2.5);
        $test6 = array(0, "hello world");
        $test7 = array(0, "0");

        $this->assertTrue($array->check($test1));
        $this->assertTrue($array->check($test2));

        $this->assertFalse($array->check($test3));
        $this->assertFalse($array->check($test4));
        $this->assertFalse($array->check($test5));
        $this->assertFalse($array->check($test6));
        $this->assertFalse($array->check($test7));


        $array->setFormat("numeric[-5, 5][+]");

        $this->assertTrue($array->check($test1));
        $this->assertTrue($array->check($test5));
        $this->assertTrue($array->check($test7));

        $this->assertFalse($array->check($test2));
        $this->assertFalse($array->check($test3));
        $this->assertFalse($array->check($test4));
        $this->assertFalse($array->check($test6));
    }

    public function testValueSetArray() {
        $array = \Structure\Structure::ArrayS("string{waiting, ready, cancelled, delivered}[4]");

        $test1 = array("waiting", "ready", "cancelled", "delivered");

        $this->assertTrue($array->check($test1));
    }

    public function testMultipleTypes1() {
        $format = array(
            "integer|float",
            "string|bool"
        );

        $array = \Structure\Structure::ArrayS($format);

        $this->assertTrue($array->check(array(5, true)));
        $this->assertTrue($array->check(array(5, "hw")));
        $this->assertTrue($array->check(array(1.5, true)));
        $this->assertTrue($array->check(array(1.5, "hw")));

        $this->assertFalse($array->check(array("", false)));
        $this->assertFalse($array->check(array(1, 1)));
    }

    public function testMultipleTypes2() {
        $array = \Structure\Structure::ArrayS("(string|integer)[]");

        $this->assertTrue($array->check(array(1, 2, 3, 4)));
        $this->assertTrue($array->check(array('a', 'b', 'c')));
        $this->assertTrue($array->check(array(1, 'b', 'c', 2, 'a', 4)));

        $this->assertTrue($array->check(array()));

        $this->assertFalse($array->check(array(1.5)));
    }

    public function testMultipleTypes3() {
        $format = array(
            "id" => "integer|null",
            "value" => "string"
        );

        $array = \Structure\Structure::ArrayS($format);

        $this->assertTrue($array->check(array(
            "id" => 1,
            "value" => "test"
        )));

        $this->assertTrue($array->check(array(
            "value" => "hw"
        )));

        // fails because the "value" key mustn't be null
        $this->assertFalse($array->check(array()));
    }

    public function testMultiDimensional() {
        $format = "integer[0,inf][3][3]"; // 3x3 matrix
        $array = \Structure\Structure::ArrayS($format);

        $validMatrix = array(
            array(1, 2, 3),
            array(4, 5, 6),
            array(7, 8, 9)
        );

        $invalidMatrix1 = array(
            array(1, 2, 3),
            array(4, -5, 6),
            array(7, 8, 9)
        );

        $this->assertTrue($array->check($validMatrix));
        $this->assertFalse($array->check($invalidMatrix1));

        $twoByThree = array(
            array(1, 2),
            array(3, 4),
            array(5, 6)
        );

        $array->setFormat("integer[2][3]");
        $this->assertTrue($array->check($twoByThree));
        $this->assertFalse($array->check($validMatrix));
        $this->assertFalse($array->check($invalidMatrix1));
    }

    public function testNullValues() {
        $array = \Structure\Structure::ArrayS(array(
            "id" => "null"
        ));

        $this->assertTrue($array->check(array("id" => null)));

        $this->assertFalse($array->check(array("id" => "abc")));
        $this->assertFalse($array->check(array("id" => 30)));
        $this->assertFalse($array->check(array("id" => array())));

        $array->setFormat(array(
            "id" => "null|integer"
        ));

        $this->assertTrue($array->check(array("id" => null)));
        $this->assertTrue($array->check(array("id" => 30)));

        $this->assertFalse($array->check(array("id" => "abc")));
        $this->assertFalse($array->check(array("id" => array())));
    }

    public function testAny() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat(array(
            "id" => "any",
            "date" => "*"
        ));

        $this->assertTrue($checker->check(array("id"=>10,"date"=>false)));
        $this->assertTrue($checker->check(array("id"=>"hello","date"=>123)));
        $this->assertTrue($checker->check(array("id"=>true,"date"=>15.3)));
        $this->assertTrue($checker->check(array("id"=>12.3,"date"=>"hello")));
        $this->assertTrue($checker->check(array("id"=>false,"date"=>false)));
        $this->assertTrue($checker->check(array("id"=>null,"date"=>null)));
        $this->assertFalse($checker->check(array()));

        $checker->setNull(true);
        $this->assertTrue($checker->check(array()));
    }

    /**
     * Test made due to concerns with php<5.6's array_fill:
     * http://php.net/array_fill
     */
    public function testZeroElements() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat("string[0]");

        $this->assertTrue($checker->check(array()));
        $this->assertFalse($checker->check(array("string")));
        $this->assertFalse($checker->check(array(1)));
        $this->assertFalse($checker->check(array(false)));
    }

    public function testNullOrStringArray() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat(array(
            "foo" => "null|string[]"
        ));
        $checker->setCountStrict(true);

        $this->assertTrue($checker->check(array()));
        $this->assertTrue($checker->check(array("foo" => null)));
        $this->assertTrue($checker->check(array("foo" => array())));
        $this->assertTrue($checker->check(array("foo" => array("hi"))));
        $this->assertTrue($checker->check(array("foo" => array("hi", "there"))));

        $this->assertFalse($checker->check(array("foo" => "bad")));
        $this->assertFalse($checker->check(array("foo" => array(null))));
        $this->assertFalse($checker->check(array("foo" => array("hi", null))));
    }

    public function testSpecificValueArray() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat(array(
            "foo" => "(string|integer{0, 1})[]"
        ));

        $this->assertTrue($checker->check(array("foo" => array("abc", "xyz"))));
        $this->assertTrue($checker->check(array("foo" => array("abc", "xyz", 0))));
        $this->assertTrue($checker->check(array("foo" => array(1, 1, 1))));
        $this->assertTrue($checker->check(array("foo" => array(0, 1, 0))));
        $this->assertFalse($checker->check(array("foo" => array("abc", "xyz", 2))));
        $this->assertFalse($checker->check(array("foo" => array("abc", false))));
    }

    public function testSpecificValueArray2() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat(array(
            "foo" => "(string|integer[1,inf))[]"
        ));

        $this->assertTrue($checker->check(array("foo" => array("abc", "xyz"))));
        $this->assertTrue($checker->check(array("foo" => array("abc", "xyz", 1))));
        $this->assertTrue($checker->check(array("foo" => array(1, 1, 1))));
        $this->assertFalse($checker->check(array("foo" => array(0, 1, 0))));
        $this->assertTrue($checker->check(array("foo" => array("abc", "xyz", 2))));
        $this->assertFalse($checker->check(array("foo" => array("abc", false))));
        $this->assertFalse($checker->check(array("foo" => array(-1, 1))));
    }

    public function testSimpleDescriptionKeyValue() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat("string[3]");

        $this->assertTrue($checker->check(array("a" => "abc", "b" => "pqr", "c" => "xyz")));
        $this->assertTrue($checker->check(array("abc", "pqr", "xyz")));
        $this->assertFalse($checker->check(array("a" => "abc", "c" => "xyz")));
        $this->assertFalse($checker->check(array("a" => "abc", "b" => 1213, "c" => "xyz")));
        $this->assertFalse($checker->check(array("abc", 1213, "xyz")));
    }

    public function testVariableLengthArray() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat("string[2..4]");

        $this->assertFalse($checker->check(array()));
        $this->assertFalse($checker->check(array("abc")));
        $this->assertTrue($checker->check(array("abc", "cde")));
        $this->assertTrue($checker->check(array("abc", "cde", "xyz")));
        $this->assertTrue($checker->check(array("abc", "cde", "xyz", "asdf")));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", "asdf", "")));

        $this->assertFalse($checker->check(array("abc", "cde", "xyz", 3)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", null)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", 3.4)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", true)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", array())));
    }

    public function testVariableLengthArrayFirstBound() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat("string[2..]");

        $this->assertFalse($checker->check(array()));
        $this->assertFalse($checker->check(array("abc")));
        $this->assertTrue($checker->check(array("abc", "cde")));
        $this->assertTrue($checker->check(array("abc", "cde", "xyz")));
        $this->assertTrue($checker->check(array("abc", "cde", "xyz", "asdf")));
        $this->assertTrue($checker->check(array("abc", "cde", "xyz", "asdf", "")));

        $this->assertFalse($checker->check(array("abc", "cde", "xyz", 3)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", null)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", 3.4)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", true)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", array())));
    }

    public function testVariableLengthArraySecondBound() {
        $checker = new \Structure\ArrayS();
        $checker->setFormat("string[..4]");

        $this->assertTrue($checker->check(array()));
        $this->assertTrue($checker->check(array("abc")));
        $this->assertTrue($checker->check(array("abc", "cde")));
        $this->assertTrue($checker->check(array("abc", "cde", "xyz")));
        $this->assertTrue($checker->check(array("abc", "cde", "xyz", "asdf")));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", "asdf", "")));

        $this->assertFalse($checker->check(array("abc", "cde", "xyz", 3)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", null)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", 3.4)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", true)));
        $this->assertFalse($checker->check(array("abc", "cde", "xyz", array())));
    }

    public function testEmptyArrayDescription() {
        $checker = \Structure\Structure::ArrayS("[0]");

        $this->assertTrue($checker->check(array()));

        $this->assertFalse($checker->check(array("abc")));
        $this->assertFalse($checker->check(array(null)));
        $this->assertFalse($checker->check("abc"));
    }
}
