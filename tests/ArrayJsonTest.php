<?php

/**
 * Created by PhpStorm.
 * User: enric
 * Date: 9/9/15
 * Time: 12:47
 */

use \Structure\ArrayS;

class ArrayJsonTest extends PHPUnit_Framework_TestCase {
    public function testJsonStructure() {
        $array = new ArrayS();
        $array->setJsonFormat(__DIR__ . "/resources/testJsonStructure.json");

        $res = $array->check(array(
            "id" => 34,
            "name" => "John",
            "age" => 18,
            "marks" => array(8, 7.6, 3, 1, 9, 0, 10, 9.8, 0.4, 4.5)
        ));

        $this->assertTrue($res);
    }
}
