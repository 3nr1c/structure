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


class StringS extends ScalarS {
    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("string");
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function format($data = null) {
        return (string)$data;
    }

    protected function toTypeFromString(&$array) {
        foreach ($array as &$value) {
            $value = trim($value);

            if (!$this->checkType($value)) {
                return false;
            }
        }
        return true;
    }
}