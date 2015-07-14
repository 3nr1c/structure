<?php
/**
 * Created by PhpStorm.
 * User: enric
 * Date: 14/7/15
 * Time: 16:47
 */

class ArrayFormatTest extends PHPUnit_Framework_TestCase {
    public function testFormat1() {
        $format = "float[]";
        $formatter = \Structure\Structure::ArrayS($format);

        $array = array("1.2","4.3",".5");

        // Check the array doesn't match the format
        $this->assertFalse($formatter->check($array));

        $newArray = $formatter->format($array);

        // Now the format is true
        $this->assertTrue($formatter->check($newArray));
    }
}
