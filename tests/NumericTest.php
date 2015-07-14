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
}
