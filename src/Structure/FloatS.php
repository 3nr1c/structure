<?php
/**
 * @author Enric Florit
 * @date 13/7/15
 */

namespace Structure;


class FloatS extends NumericS {
    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("float");
    }
}