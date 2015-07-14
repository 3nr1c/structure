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
}
