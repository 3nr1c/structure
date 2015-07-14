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
