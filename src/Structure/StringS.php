<?php
/**
 * Created by PhpStorm.
 * User: enric
 * Date: 13/7/15
 * Time: 16:23
 */

namespace Structure;


class StringS extends ScalarS {
    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("string");
    }
}