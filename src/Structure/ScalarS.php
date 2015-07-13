<?php
/**
 * @author Enric Florit
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
    public function checkType($data = null) {
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
            case "bool":
                return is_bool($this->getData());
        }
    }
}