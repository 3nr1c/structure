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

/**
 * Class ScalarS
 * @package Structure
 */
class ScalarS extends Structure {
    /**
     * @var array
     */
    protected $valueSet = array();

    /**
     * Saves the information extracted from value set strings
     * to avoid re-parsing them in the future
     * @var array
     */
    protected static $compiledValueSets = array();

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

    /**
     * @param mixed $data
     * @return bool
     */
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
     * Runs type and value set tests
     *
     * @api
     *
     * @param mixed $data
     * @return boolean
     */
    public function check($data = null) {
        return $this->checkType($data) && $this->checkValueSet($data);
    }

    /**
     * @api
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

    /**
     * @api
     * @return bool
     * @throws \Exception
     */
    public function setValueSet() {
        $argc = func_num_args();
        $argv = func_get_args();

        if ($argc === 1 && is_string($argv[0])) {
            // try to parse a string with structure "{$elem1, $elem2}"
            $arg = $argv[0];

            if (isset(ScalarS::$compiledValueSets[$arg])) {
                $valueSet = ScalarS::$compiledValueSets[$arg];
                goto set_info;
            }

            if ($arg[0] !== "{") {
                throw new \Exception("Value set definition must start with '{'");
            }

            $matchedBracket = false;
            $valueSet = array("");

            // parse the string argument
            for ($i = 1; $i < strlen($arg); $i++) {
                if ($arg[$i] === '}' && $arg[$i - 1] !== '\\') {
                    $matchedBracket = true;
                } else if ($matchedBracket) {
                    throw new \Exception("Unexpected character ' ' after '}'");
                } else if ($arg[$i] === ',' && $arg[$i - 1] !== '\\') {
                    $valueSet[] = "";
                } else if ($arg[$i] === '\\') {
                    if ($i + 1 < strlen($arg) && $arg[$i + 1] !== ',' && $arg[$i + 1] !== '}') {
                        $valueSet[count($valueSet) - 1] .= "\\";
                    }
                } else {
                    $valueSet[count($valueSet) - 1] .= $arg[$i];
                }
            }

            if (!$matchedBracket) throw new \Exception("Expected character '}' at the end of value set string");

            NumericS::$compiledValueSets[$arg] = $valueSet;
set_info:
            if (!$this->toTypeFromString($valueSet)) {
                return false;
            } else {
                $this->valueSet = $valueSet;
                return true;
            }

        } else if (Structure::ArrayS($this->getType() . "[]")->check($argv)) {
            foreach ($argv as $value) {
                if (!$this->checkType($value)) return false;
            }
            $this->valueSet = $argv;
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