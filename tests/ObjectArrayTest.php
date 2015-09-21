<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Enric Florit
 * @since 0.6.0
 */

class ObjectArrayTest extends PHPUnit_Framework_TestCase {
    public function testFreeTypeArray() {
        $array = new Structure\ArrayS();
        $array->setFormat("[]");

        $tester = array(new Tester(), new Tester(), new BadTester());

        $this->assertTrue($array->check($tester));
    }

    public function testConcreteObjectArray() {
        $array = new Structure\ArrayS();
        $array->setFormat("Tester[]");

        $tester = array(new Tester(), new Tester());
        $this->assertTrue($array->check($tester));

        $badTester = array(new Tester(), new Tester(), new BadTester());
        $this->assertFalse($array->check($badTester));
    }
}

class Tester {}

class BadTester {}