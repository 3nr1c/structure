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


class IntegerS extends NumericS {
    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("integer");
    }

    /**
     * @param mixed $data
     * @return int
     */
    public function format($data = null) {
        $data = parent::format($data);

        if (!is_null($data)) return intval($data);
        else return null;
    }
}