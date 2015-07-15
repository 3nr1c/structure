<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Enric Florit
 * @since 0.2.0
 * @date 14/7/15
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

    public function testFormat2() {
        $format = array(
            "foo" => "boolean",
            "bar" => "float"
        );

        $formatter = \Structure\Structure::ArrayS($format);

        $array = array(
            "foo" => "false",
            "bar" => "1.3"
        );

        $this->assertFalse($formatter->check($array));

        $newArray = $formatter->format($array);

        $this->assertTrue($formatter->check($newArray));
    }

    public function testFormat3() {
        $format = array(
            "full_name" => "string",
            "height" => "integer",
            "weight" => "integer",
            "gender" => "integer[0,2]"
        );
        $formatter = \Structure\Structure::ArrayS($format, null, false, true);

        $array = array(
            "full_name" => "My Name",
            "height" => 175,
            "gender" => "-1"
        );

        $this->assertFalse($formatter->check($array));
        $newArray = $formatter->format($array);
        $this->assertTrue($formatter->check($newArray));
    }
}
