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
     * @param $failed
     * @return bool
     */
    protected function checkType($data = null, &$failed = null) {
        if (is_null($data)) {
            $data = $this->getData();
        }

        if ($this->getNull() && is_null($data)) {
            return true;
        }

        switch ($this->getType()) {
            default:
                $valid = false;
                break;
            case "scalar":
                $valid = is_scalar($data);
                break;
            case "numeric":
                $valid = is_numeric($data);
                break;
            case "string":
                $valid = is_string($data);
                break;
            case "integer":
                $valid = is_integer($data);
                break;
            case "float":
                $valid = is_float($data) || is_integer($data);// numbers without floating point crash
                break;
            case "boolean":
                $valid = is_bool($data);
                break;
        }

        if (!$valid) $failed = Structure::typeof($data);
        return $valid;
    }

    /**
     * @param mixed $data
     * @param $failed
     * @return bool
     */
    protected function checkValueSet($data = null, &$failed = null) {
        if (is_null($data)) {
            $data = $this->getData();
        }

        if (count($this->getValueSet()) > 0) {
            if ($this->getType() === "string" && is_string($data)) {
                $data = trim($data);
            }

            // search in the $valueSet with strict comparison
            if (!in_array($data, $this->getValueSet(), true)) {
                $failed = $this->getType() . ":value";
                return false;
            }
        }
        return true;
    }

    /**
     * Runs type and value set tests
     *
     * @api
     *
     * @param mixed $data
     * @param $failed
     * @return boolean
     */
    public function check($data = null, &$failed = null) {
        $valid = $this->checkType($data, $failed) && $this->checkValueSet($data, $failed);

        if (!$valid) Structure::$lastFail = $failed;
        return $valid;
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
                if ($matchedBracket) {
                    throw new \Exception("Unexpected character '" . $arg[$i] . "' after '}'");
                } else if ($arg[$i] === '}' && $arg[$i - 1] !== '\\') {
                    $matchedBracket = true;
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
            } else if ($value === "\\true" || $value === "\\false") {
                $value = substr($value, 1);
            } else if (preg_match('/^-?[0-9]+(\.[0-9]*)?$/', $value)) {
                // convert to float or integer
                if ((float)$value == (int)$value && strpos($value, ".") === false)
                    $value = (int)$value;
                else $value = (float)$value;
            }

            if (!$this->checkType($value)) {
                return false;
            }
        }
        return true;
    }

    public static function isScalarType($type) {
        $scalarTypes = array("scalar", "numeric", "string", "integer", "float", "boolean", "bool",
            // three character types are used in ArrayS::checkValue
            "sca", "num", "str", "int", "flo", "boo");
        return in_array($type, $scalarTypes);
    }
}