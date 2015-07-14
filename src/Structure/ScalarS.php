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


class ScalarS extends Structure {
    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct("scalar", $data, $null);
    }

    /**
     * @param mixed $data
     * @return bool
     */
    protected function checkType($data = null) {
        if (!is_null($data)) {
            $this->setData($data);
        }

        if ($this->getNull() && is_null($this->getData())) {
            return true;
        }

        switch ($this->getType()) {
            default:
                return false;
            case "scalar":
                return is_scalar($this->getData());
            case "numeric":
                return is_numeric($this->getData());
            case "string":
                return is_string($this->getData());
            case "integer":
                return is_integer($this->getData());
            case "float":
                return is_float($this->getData());
            case "boolean":
                return is_bool($this->getData());
        }
    }

    /**
     * @param mixed $data
     * @return boolean
     */
    public function check($data = null) {
        return $this->checkType($data);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function format($data = null) {
        settype($data, $this->getType());
        return $data;
    }
}