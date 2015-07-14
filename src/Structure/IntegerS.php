<?php
/**
 * @author Enric Florit
 * @date 13/7/15
 */

namespace Structure;


class IntegerS extends NumericS {
    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("integer");
    }

    public function format($data = null) {
        return intval($data);
    }
}