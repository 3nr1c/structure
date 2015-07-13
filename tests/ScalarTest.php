<?php
/**
 * @author Enric Florit
 * @date 13/7/15
 */

class ScalarTest extends PHPUnit_Framework_TestCase {
    public function testCheckType() {
        $scalar = new \Structure\ScalarS();

        $scalar->setData("Hello world");
        $this->assertTrue($scalar->checkType());

        $scalar->setData(array());
        $this->assertFalse($scalar->checkType());
    }
}
