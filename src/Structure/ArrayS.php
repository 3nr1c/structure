<?php
/**
 * @author Enric Florit
 * @date 13/7/15
 */

namespace Structure;


class ArrayS extends Structure {
    private $format;

    public function __construct($data = null, $null = false) {
        parent::__construct("array", $data, $null);
    }

    /**
     * @param array $format
     */
    public function setFormat($format) {
        $this->format = $format;
    }

    /**
     * @return array
     */
    public function getFormat() {
        return $this->format;
    }

    public function checkType($data = null) {
        if (!is_null($data)) $this->data = $data;

        return is_array($this->data);
    }

    /**
     * It assumes that $data is an array
     * @param mixed $data
     * @return bool
     * @throws \Exception
     */
    public function checkFormat($data = null) {
        if (!is_null($data)) $this->data = $data;

        if (count($this->data) !== count($this->format)) return false;

        $associativeData = ArrayS::isAssociative($this->data);
        $associativeFormat = ArrayS::isAssociative($this->format);

        if ($associativeData && $associativeFormat) {
            foreach ($this->getFormat() as $key=>$value) {
                if (!array_key_exists($key, $this->data)) {
                    $valid = false;
                } else {
                    $valid = $this->checkValue($this->data[$key], $value);
                }
                if (!$valid) return false;
            }
            return true;
        } else if (!$associativeData && !$associativeFormat) {
            for ($i = 0; $i < count($this->format); $i++) {
                $valid = $this->checkValue($this->data[$i], $this->format[$i]);
                if (!$valid) return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function check($data = null) {
        if ($this->getNull()) {
            return (is_null($this->data) || $this->checkType($data)) && $this->checkFormat($data);
        } else {
            return $this->checkType($data) && $this->checkFormat($data);
        }
    }

    protected function checkValue($data, $format) {
        $numeric = '/^(numeric|float|integer)(\(|\[)-?\d+(\.\d+)?,-?\d+(\.\d+)?(\)|\])$/';

        if (is_null($data)) {
            $valid = $this->getNull();
        } else if (is_string($format)) {
            if (preg_match($numeric, $format)) {
                switch ($format[0]) {
                    case "n":
                        $structure = new NumericS();
                        break;
                    case "f":
                        $structure = new FloatS();
                        break;
                    case "i":
                        $structure = new IntegerS();
                        break;
                }
                /** @var NumericS $structure */
                $structure->setRange(preg_replace("/^(numeric|float|integer)/", "", $format));
                $valid = $structure->check($data);
            } else {
                switch ($format) {
                    case "scalar":
                        $valid = is_scalar($data);
                        break;
                    case "string":
                    case "str":
                        $valid = is_string($data);
                        break;
                    case "numeric":
                        $valid = is_numeric($data);
                        break;
                    case "integer":
                    case "int":
                        $valid = is_integer($data);
                        break;
                    case "float":
                        $valid = is_float($data);
                        break;
                    case "boolean":
                    case "bool":
                        $valid = is_bool($data);
                        break;
                    case "array":
                        $valid = is_array($data);
                        break;
                    case "*":
                    case "any":
                        $valid = true;
                        break;
                    default:
                        if (class_exists($format)) {
                            $valid = $data instanceof $format;
                        } else {
                            $valid = true;
                        }
                        break;
                }
            }
        } else if (is_array($format)) {
            $a = new ArrayS($data, $this->getNull());
            $a->setFormat($format);
            $valid = $a->check();
        } else {
            $valid = true;
        }

        return $valid;
    }

    /**
     * Source: http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     * @param array $data
     * @return bool
     */
    public static function isAssociative($data) {
        return array_keys($data) !== range(0, count($data) - 1);
    }
}