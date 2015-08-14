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


class ArrayS extends Structure {
    protected $format;
    protected $countStrict = true;

    protected $minimumItemNumber = 0;

    protected $containsNullItems = null;

    public function __construct($data = null, $null = false) {
        parent::__construct("array", $data, $null);
    }

    /**
     * @param array|string[] $format
     * @throws \Exception
     */
    public function setFormat($format) {
        if (is_array($format)) {
            $this->format = $format;
        } else if (is_string($format)) {
            if ($format === "array") {
                $this->format = $format;
                return;
            }

            $sqBrackets = '\[(\d+|\d*\+|\*)?\]';

            $singleType = '/^([^\|]*)(' . $sqBrackets . ')$/';
            $multipleType = '/^\((.*)\)(' . $sqBrackets . ')$/';

            if (preg_match($singleType, $format, $matches) || preg_match($multipleType, $format, $matches)) {
                $this->format = $matches[1];
                $this->parseArraySqBrackets($matches[2]);
            } else {
                $this->format = $format;
            }

            if ($this->format === "") $this->format = "*";
        }
    }

    /**
     * Parses expressions matching /\[(\d+|\d*\+|\*)?\]/
     *
     * @param string $sqBrackets
     */
    protected function parseArraySqBrackets($sqBrackets) {
        $length = strlen($sqBrackets);
        if ($sqBrackets === '[]' || $sqBrackets === '[*]') {
            $this->minimumItemNumber = 0;
        } else if ($sqBrackets === '[+]') {
            $this->minimumItemNumber = 1;
        } else if ($length > 2 && $sqBrackets[$length - 2] === '+') {// [n+] case
            $this->minimumItemNumber = substr($sqBrackets, 1, -2);
        } else {// [n] case
            $count = substr($sqBrackets, 1, -1);
            $this->format = array_fill(0, intval($count), $this->format);
        }
    }


    protected function searchNullItems() {
        if (is_null($this->containsNullItems)) {
            $this->containsNullItems = false;
            foreach ($this->format as $item) {
                if (is_string($item) && strpos($item, "null") !== false) {
                    $this->containsNullItems = true;
                    break;
                }
            }
        }

        return $this->containsNullItems;
    }

    /**
     * @return array
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * @return boolean
     */
    public function isCountStrict() {
        return $this->countStrict;
    }

    /**
     * @param boolean $countStrict
     */
    public function setCountStrict($countStrict) {
        $this->countStrict = $countStrict;
    }

    /**
     * Runs an __is_array__ test on some $data.
     * A test format can be set (read Documentation)
     *
     * @param mixed $data
     * @return bool
     */
    public function check($data = null) {
        if (is_null($data)) $data = $this->data;
        if (!isset($this->format)) $this->format = "array";

        if ($this->getNull()) {
            return (is_null($data) || $this->checkType($data)) && $this->checkFormat($data);
        } else {
            return $this->checkType($data) && $this->checkFormat($data);
        }
    }

    /**
     * @param mixed $data
     * @param mixed $format
     * @return array
     * @throws \Exception
     */
    public function format($data = null, $format = null) {
        if (!is_null($data)) $this->setData($data);
        if (!is_null($format)) $this->setFormat($format);

        if (!is_array($this->data)) {
            throw new \Exception("Array format only available for arrays");
        }

        $this->applyFormat();
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return bool
     */
    protected function checkType($data = null) {
        if (is_null($data)) $data = $this->data;

        return is_array($data);
    }

    /**
     * It assumes that $data is an array
     * @param mixed $data
     * @return bool
     * @throws \Exception
     */
    protected function checkFormat($data = null) {
        if (is_null($data)) $data = $this->data;

        if ($this->format === "array") {
            return true;// no need to run again is_array
        }

        if (is_string($this->format)) {
            foreach ($data as $value) {
                $valid = $this->checkValue($value, $this->format);
                if (!$valid) return false;
            }
            return $this->getNull() ? true : count($data) >= $this->minimumItemNumber;
        }

        /**
         * countStrict behavior:
         *  - if $data has more entries than the $format, it's up to $countStrict
         *  - if $data has the same entries as the $format, ok
         *  - if $data has less entries than the $format, the string "null" is searched
         *      in $format
         */
        if (!$this->getNull() && $this->isCountStrict()){
            if (count($data) > count($this->format) ||
                (count($data) < count($this->format) && !$this->searchNullItems())) {
                return false;
            }
            // else: ok
        }

        $associativeData = ArrayS::isAssociative($data);
        $associativeFormat = ArrayS::isAssociative($this->format);

        if ($associativeData && $associativeFormat) {
            foreach ($this->getFormat() as $key=>$value) {
                $testData = array_key_exists($key, $data) ? $data[$key] : null;
                $valid = $this->checkValue($testData, $value);
                if (!$valid) return false;
            }
            return true;
        } else if (!$associativeData && !$associativeFormat) {
            for ($i = 0; $i < count($data); $i++) {
                $valid = $this->checkValue($data[$i], $this->format[$i]);
                if (!$valid) return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $data
     * @param mixed $format
     * @param bool $applyFormat
     * @return bool|array
     * @throws \Exception
     */
    protected function checkValue($data, $format, $applyFormat = false) {
        if (is_null($data) && !$this->searchNullItems()) {
            $valid = $this->getNull();
        } else if (is_string($format)) {
            return $this->checkValueStringFormat($data, $format, $applyFormat);
        } else if (is_array($format)) {
            $a = new ArrayS($data, $this->getNull());
            $a->setCountStrict($this->countStrict);
            $a->setFormat($format);
            $valid = $a->check();
        } else {
            $valid = true;
        }

        return $valid;
    }

    /**
     * @param mixed $data
     * @param string $format
     * @param bool|false $applyFormat
     * @return bool|array
     * @throws \Exception
     */
    protected function checkValueStringFormat($data, $format, $applyFormat = false) {
        /**
         * Examples:
         *  numeric[-10, 30)
         *  integer(0, inf)
         *  float[0,1]
         */
        $numeric = '/^(numeric|float|integer|int)(\(|\[).+,[^\]]+(\)|\])$/';
        /**
         * Examples:
         *  string{pendent, payed, ready, cancelled}
         *  integer{10, 100, 1000}
         *  scalar{0, false}
         */
        $valueSetScalar = '/^(scalar|string|float|integer|int|str|boolean|bool|numeric)\{[^}]*\}$/';


        // allow "type1|type2|..."
        $types = explode("|", $format);
        if (count($types) > 1 && $applyFormat) {
            throw new \Exception("Can't format \$data to multiple types");
        }

        // Use a do-while to check always $type[0]
        $i = 0;
        do {
            $type = $types[$i];
            if (preg_match($numeric, $type)) {
                switch ($type[0]) {
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
                $structure->setNull($this->getNull());
                $structure->setRange(preg_replace("/^(numeric|float|integer)/", "", $type));
                if ($applyFormat) {
                    return $structure->format($data);
                } else {
                    $valid = $structure->check($data);
                }
            } else if (preg_match($valueSetScalar, $type)) {
                switch ($type[0] . $type[1]) {
                    default:
                    case "sc":
                        $structure = new ScalarS();
                        break;
                    case "st":
                        $structure = new StringS();
                        break;
                    case "nu":
                        $structure = new NumericS();
                        break;
                    case "fl":
                        $structure = new FloatS();
                        break;
                    case "in":
                        $structure = new IntegerS();
                        break;
                    case "bo":
                        $structure = new BooleanS();
                        break;
                }
                /** @var ScalarS $structure */
                $structure->setNull($this->getNull());
                $structure->setValueSet(preg_replace('/^[^{]+/', "", $type));
                if ($applyFormat) {
                    return $structure->format($data);
                } else {
                    $valid = $structure->check($data);
                }
            } else {
                switch ($type) {
                case "scalar":
                    $structure = new ScalarS();
                    break;
                case "string":
                case "str":
                    $structure = new StringS();
                    break;
                case "numeric":
                    $structure = new NumericS();
                    break;
                case "integer":
                case "int":
                    $structure = new IntegerS();
                    break;
                case "float":
                    $structure = new FloatS();
                    break;
                case "boolean":
                case "bool":
                    $structure = new BooleanS();
                    break;
                case "array":
                    $structure = new ArrayS();
                    $structure->setFormat("array");
                    $structure->setCountStrict($this->countStrict);
                    break;
                case "*":
                case "any":
                    if ($applyFormat) return $data;
                    $valid = true;
                    break;
                case "null":
                    if ($applyFormat) return null;
                    $valid = is_null($data);
                    break;
                default:
                    if (class_exists($format)) {
                        if ($applyFormat) return $data;
                        $valid = $data instanceof $format;
                    } else {
                        // maybe $format is a simple array (type[] or type[int])
                        $structure = new ArrayS();
                        $structure->setFormat($format);
                        $structure->setCountStrict($this->countStrict);
                    }
                    break;
                }
                /** @var Structure $structure */
                if (isset($structure)) {
                    $structure->setNull($this->getNull());

                    if ($applyFormat) {
                        return $structure->format($data);
                    } else if (!isset($valid) || !$valid) {
                        try {
                            $valid = $structure->check($data);
                        } catch (\Exception $e) {
                            $valid = false;
                        }
                    }
                } else {
                    $valid = true;
                }
            }
        } while(!$valid and count($types) > ++$i);

        return $valid;
    }

    /**
     * @throws \Exception
     */
    protected function applyFormat() {
        if (is_string($this->format)) {
            foreach ($this->data as &$value) {
                $value = $this->checkValue($value, $this->format, true);
            }
            return true;
        }

        if ($this->isCountStrict() && count($this->data) !== count($this->format)) {
            throw new \Exception("countStrict doesn't allow comparisons between \$data and \$format");
        }

        $associativeData = ArrayS::isAssociative($this->data);
        $associativeFormat = ArrayS::isAssociative($this->format);

        if ($associativeData && $associativeFormat) {
            foreach ($this->getFormat() as $key=>$value) {
                if (!array_key_exists($key, $this->data)) {
                    if ($this->null) {
                        $this->data[$key] = null;
                    } else {
                        throw new \Exception("Undefined key '" . $key . "'");
                    }
                } else {
                    $this->data[$key] = $this->checkValue($this->data[$key], $value, true);
                }
            }
            return true;
        } else if (!$associativeData && !$associativeFormat) {
            for ($i = 0; $i < count($this->format); $i++) {
                $this->data[$i] = $this->checkValue($this->data[$i], $this->format[$i], true);
            }
            return true;
        } else {
            if ($associativeData) {
                throw new \Exception("Error to trying to format an associative array to sequential");
            } else {
                throw new \Exception("Error to trying to format a sequential array to associative");
            }
        }
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