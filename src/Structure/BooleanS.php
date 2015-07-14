<?php
/**
 * Created by PhpStorm.
 * User: enric
 * Date: 14/7/15
 * Time: 14:52
 */

namespace Structure;


class BooleanS extends ScalarS {
    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("boolean");
    }

    public function format($data = null) {
        return boolval($data);
    }
}