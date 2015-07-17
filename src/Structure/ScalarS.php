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
    protected $valueSet = array();

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
        if (is_null($data)) {
            $data = $this->getData();
        }

        if ($this->getNull() && is_null($data)) {
            return true;
        }

        switch ($this->getType()) {
            default:
                return false;
            case "scalar":
                return is_scalar($data);
            case "numeric":
                return is_numeric($data);
            case "string":
                return is_string($data);
            case "integer":
                return is_integer($data);
            case "float":
                return is_float($data) || is_integer($data);// numbers without floating point crash
            case "boolean":
                return is_bool($data);
        }
    }

    protected function checkValueSet($data = null) {
        if (is_null($data)) {
            $data = $this->getData();
        }

        if (count($this->getValueSet()) === 0) {
            return true;
        } else {
            if ($this->getType() === "string" && is_string($data)) {
                $data = trim($data);
            }
            return in_array($data, $this->getValueSet(), true);
        }
    }

    /**
     * @param mixed $data
     * @return boolean
     */
    public function check($data = null) {
        return $this->checkValueSet($data) && $this->checkType($data);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function format($data = null) {
        settype($data, $this->getType());

        if (!$this->checkValueSet($data)) {
            return $this->getValueSet()[0];
        } else {
            return $data;
        }
    }

    public function setValueSet() {
        $argv = func_get_args();

        if (Structure::ArrayS("string[1]")->check($argv)) {
            // try to parse a string with structure "{$elem1, $elem2}"
            $arg = $argv[0];

            if ($arg[0] !== "{") {
                throw new \Exception("Value set definition must start with '{'");
            }

            $matchedBracket = false;
            $valueSet = array("");

            // parse the string argument
            for ($i = 1; $i < strlen($arg); $i++) {
                if ($arg[$i] === '}') {
                    $matchedBracket = true;
                } else if ($matchedBracket) {
                    throw new \Exception("Unexpected character ' ' after '}'");
                } else if ($arg[$i] === ',') {
                    $valueSet[] = "";
                } else {
                    $valueSet[count($valueSet) - 1] .= $arg[$i];
                }
            }

            if (!$matchedBracket) throw new \Exception("Expected character '}' at the end of value set string");


            if (!$this->toTypeFromString($valueSet)) {
                return false;
            } else {
                $this->valueSet = array_unique($valueSet);
                return true;
            }

        } else if (Structure::ArrayS($this->getType() . "[]")->check($argv)) {
            foreach ($argv as $value) {
                if (!$this->checkType($value)) return false;
            }
            $this->valueSet = array_unique($argv);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getValueSet() {
        return $this->valueSet;
    }

    protected function toTypeFromString(&$array) {
        foreach ($array as &$value) {
            $value = trim($value);

            if ($value === "true" || $value === "false") {
                // convert to boolean
                $value = ($value === "true");
            } else if (preg_match('/^-?[0-9]+(\.[0-9]*)?$/', $value)) {
                // convert to float or integer
                if ((float)$value == (int)$value) $value = (int)$value;
                else $value = (float)$value;
            }

            if (!$this->checkType($value)) {
                return false;
            }
        }
        return true;
    }

}