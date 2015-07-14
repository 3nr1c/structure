<?php
/**
 * Created by PhpStorm.
 * User: enric
 * Date: 14/7/15
 * Time: 10:17
 */

class StaticsTest extends PHPUnit_Framework_TestCase {
    public function testStaticArrayS() {
        $format = array(
            "int[5]",
            "float[5]",
            "bool"
        );

        $array = \Structure\Structure::ArrayS($format);

        $data = array(
            range(1, 5),
            range(1.2, 2, 0.2),
            true
        );

        $this->assertTrue($array->check($data));
    }
}
