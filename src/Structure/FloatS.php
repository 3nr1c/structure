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

    /**
     * @param mixed $data
     * @return float
     */
    public function format($data = null) {
        $data = parent::format($data);

        if (!is_null($data)) return floatval($data);
        else return null;
    }
}