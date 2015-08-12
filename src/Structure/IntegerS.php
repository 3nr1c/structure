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
    protected $numeric = false;

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

    /**
     * @param bool $numeric
     */
    public function setNumeric($numeric) {
        $this->numeric = $numeric;
    }

    /**
     * @return bool
     */
    public function getNumeric() {
        return $this->numeric;
    }

    /**
     * @param null $data
     * @return bool
     */
    public function checkType($data = null) {
        if (!$this->getNumeric()) {
            return parent::checkType($data);
        } else {
            if (is_null($data)) {
                $data = $this->getData();
            }

            if ($this->getNull() && is_null($data)) {
                return true;
            }

            $valid = is_numeric($data);
            $valid = $valid && (float)$data == (int)$data;
            return $valid;
        }
    }
}