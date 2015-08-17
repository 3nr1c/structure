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

class NumericTest extends PHPUnit_Framework_TestCase {
    public function testType() {
        $numeric = new \Structure\NumericS();

        $this->assertTrue($numeric->check(3));

        $this->assertTrue($numeric->check(3.2));

        $this->assertTrue($numeric->check("1"));

        $this->assertTrue($numeric->check("5.7"));

        $this->assertTrue($numeric->check("5."));

        $this->assertFalse($numeric->check(array()));
        $this->assertFalse($numeric->check("1.."));
        $this->assertFalse($numeric->check("hello"));

        $this->assertFalse($numeric->check(null));
        $numeric->setNull(true);
        $this->assertTrue($numeric->check(null));
    }

    public function testParser1() {
        $range = "  [ -13, 4.2)";
        try {
            $numeric = \Structure\Structure::NumericS($range);
        } catch (\Exception $e) {
            $this->fail("Unexpected exception: '" . $e->getMessage() . "'");
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unexpected character '{'
     */
    public function testParser2() {
        \Structure\Structure::NumericS("{3, 4.2]");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unexpected space character ' '
     */
    public function testParser3() {
        \Structure\Structure::NumericS("[3. 2, 4.2]");
    }

    public function testCorrectRange1() {
        $numeric = new \Structure\NumericS();

        $validRanges = array(
            "[51,71]",
            "[-51,71]",
            "[-71,-51]",
            "(51,71]",
            "(-51,71]",
            "(-71,-51]",
            "[51,71)",
            "[-51,71)",
            "[-71,-51)",
            "(51,71)",
            "(-51,71)",
            "(-71,-51)"
        );

        $midValues = array(60, 0, -60);
        $lowValues = array(51, -51, -71);
        $uppValues = array(71, 71, -51);

        // Shouldn't throw any exceptions
        foreach ($validRanges as $i=>$range) {
            $numeric->setRange($range);
            $this->assertTrue($numeric->check($midValues[$i % 3]));

            if ($i % 6 < 3) {
                $this->assertTrue($numeric->check($lowValues[$i % 3]));
            } else {
                $this->assertFalse($numeric->check($lowValues[$i % 3]));
            }

            if ($i < 6) {
                $this->assertTrue($numeric->check($uppValues[$i % 3]));
            } else {
                $this->assertFalse($numeric->check($uppValues[$i % 3]));
            }
        }
    }

    public function testCorrectRange2() {
        $numeric = new \Structure\NumericS();

        $validRanges = array(
            "[13.4,24.2]",
            "[-13.4,24.2]",
            "[-24.2,-13.4]",
            "(13.4,24.2]",
            "(-13.4,24.2]",
            "(-24.2,-13.4]",
            "[13.4,24.2)",
            "[-13.4,24.2)",
            "[-24.2,-13.4)",
            "(13.4,24.2)",
            "(-13.4,24.2)",
            "(-24.2,-13.4)"
        );

        $midValues = array(20, 0, -20);
        $lowValues = array(13.4, -13.4, -24.2);
        $uppValues = array(24.2, 24.2, -13.4);

        // Shouldn't throw any exceptions
        foreach ($validRanges as $i=>$range) {
            $numeric->setRange($range);
            $this->assertTrue($numeric->check($midValues[$i % 3]));

            if ($i % 6 < 3) {
                $this->assertTrue($numeric->check($lowValues[$i % 3]));
            } else {
                $this->assertFalse($numeric->check($lowValues[$i % 3]));
            }

            if ($i < 6) {
                $this->assertTrue($numeric->check($uppValues[$i % 3]));
            } else {
                $this->assertFalse($numeric->check($uppValues[$i % 3]));
            }
        }
    }

    public function testWrongRange1() {
        $numeric = new \Structure\NumericS();

        $wrongRanges = array(
            "",
            "f",
            "(",
            "[1",
            "[1,)",
            "(1..,",
            "]"
        );

        $withoutException = 0;
        foreach ($wrongRanges as $range) {
            try {
                $numeric->setRange($range);
                $withoutException++;
            } catch(\Exception $e) {
                $this->addToAssertionCount(1);
            }
        }

        if ($withoutException > 0) {
            $this->fail($withoutException . " non-thrown exceptions");
        }
    }

    public function testValueSet() {
        $float = new Structure\FloatS();
        $this->assertTrue($float->setValueSet("{10.1,20.1,30.1,40.1,50.1,60.1,70.1,80.1,90.1}"));

        foreach (range(10.1, 90.1, 10) as $i) {
            $this->assertTrue($float->check($i));
        }
    }

    public function testInfinities() {
        $numeric = new Structure\NumericS();
        $numeric->setRange("(-inf, inf)");
        for ($i = -pow(10, 10); $i < pow(10,10); $i += pow(10, 9)) {
            $this->assertTrue($numeric->check($i));
        }

        $numeric->setRange("(0, +inf)");
        $this->assertFalse($numeric->check(0));
        $this->assertTrue($numeric->check(1));
        $this->assertTrue($numeric->check(147821764713627581));

        $numeric->setRange("[1,inf)");
        $this->assertFalse($numeric->check(0));
        $this->assertTrue($numeric->check(1));
        $this->assertTrue($numeric->check(147821764713627581));
    }

    public function testBadInfinities() {
        try {
            $numeric = \Structure\Structure::NumericS("(inf, 0)");
            $this->fail("An exception was excepted");
        } catch(\Exception $e) {
            $this->addToAssertionCount(1);
        }

        try {
            $numeric = \Structure\Structure::NumericS("(0,-inf)");
            $this->fail("An exception was excepted");
        } catch(\Exception $e) {
            $this->addToAssertionCount(1);
        }
    }

    public function testIntegerSNumeric() {
        $integer = new \Structure\IntegerS();

        $this->assertTrue($integer->check(1));
        $this->assertFalse($integer->check("1"));
        $this->assertFalse($integer->check(1.2));
        $this->assertFalse($integer->check("1.2"));

        $integer->setNumeric(true);

        $this->assertTrue($integer->check(1));
        $this->assertTrue($integer->check("1"));
        $this->assertFalse($integer->check(1.2));
        $this->assertFalse($integer->check("1.2"));
    }

    public function testFloatSNumeric() {
        $float = new \Structure\FloatS();

        $this->assertTrue($float->check(1));
        $this->assertFalse($float->check("1"));
        $this->assertTrue($float->check(1.2));
        $this->assertFalse($float->check("1.2"));

        $float->setNumeric(true);

        $this->assertTrue($float->check(1));
        $this->assertTrue($float->check("1"));
        $this->assertTrue($float->check(1.2));
        $this->assertTrue($float->check("1.2"));
    }
}
