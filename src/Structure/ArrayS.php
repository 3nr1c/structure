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
 * Class ArrayS
 * @package Structure
 */
class ArrayS extends Structure {
    protected $format;
    protected $countStrict = true;

    protected $minimumItemNumber = 0;
    protected $containsNullItems = null;

    protected static $compiledFormats = array();

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

            if ($this->format === "" || $this->format === "*") {
                $this->format = "array";
            }
        }
    }

    /**
     * Takes a string argument that can be either a valid JSON string, or
     * a path to a file containing a valid JSON string. If the syntax is
     * invalid or the file can't be accessed, an \Exception will be thrown
     *
     * @param string $format
     * @return bool
     * @throws \Exception
     */
    public function setJsonFormat($format) {
        if (!is_string($format)) throw new \Exception("JsonFormat must be a valid json string or a path to a file");

        if (file_exists($format)) {
            $handler = fopen($format, 'r');
            $contents = fread($handler, filesize($format));
            fclose($handler);

            $format = $contents;
        }
        $format = trim($format);

        $arrayFormatCandidate = json_decode($format, true, 1024);

        $error = json_last_error();
        if ($error === JSON_ERROR_NONE) {
            $this->setFormat($arrayFormatCandidate);
            return true;
        }

        throw new \Exception("Invalid Json format or file");
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
            $count = intval(substr($sqBrackets, 1, -1));
            $this->format = $count > 0 ? array_fill(0, $count, $this->format) : array();
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
     * @param array $failed
     * @return bool
     */
    public function check($data = null, &$failed = array()) {
        if (is_null($data)) $data = $this->data;
        if (!isset($this->format)) $this->format = "array";

        // reset the reporting array
        $failed = array();

        if ($this->getNull()) {
            $valid = (is_null($data) || $this->checkType($data, $failed)) && $this->checkFormat($data, $failed);
        } else {
            $valid = $this->checkType($data, $failed) && $this->checkFormat($data, $failed);
        }
        // save the failures for later checking
        if (!is_array($failed) || is_array($failed) && count($failed) > 0) {
            Structure::$lastFail = $failed;
        }
        return $valid;
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
     * @param $failed
     * @return bool
     */
    protected function checkType($data = null, &$failed = null) {
        if (is_null($data)) $data = $this->data;

        $valid = is_array($data);

        if (!$valid) $failed = Structure::typeof($data);
        return $valid;
    }

    /**
     * It assumes that $data is an array
     * @param mixed $data
     * @param array $failed
     * @return bool
     * @throws \Exception
     */
    protected function checkFormat($data = null, &$failed = array()) {
        if (is_null($data)) $data = $this->data;

        if ($this->format === "array") {
            return true;// no need to run again is_array
        }

        if (is_string($this->format)) {
            $valid = true;
            foreach ($data as $key=>$value) {
                if (!$this->checkValue($value, $this->format, false, $valueFailed)) {
                    $failed[$key] = $valueFailed;
                    $valid = false;
                }
            }
            return $valid && count($data) >= $this->minimumItemNumber;
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

        if ($associativeData == $associativeFormat) {
            $valid = true;
            foreach ($this->getFormat() as $key=>$value) {
                $testData = isset($data[$key]) ? $data[$key] : null;
                if (!$this->checkValue($testData, $value, false, $valueFailed)) {
                    $failed[$key] = $valueFailed;
                    $valid = false;
                }
            }
            return $valid;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $data
     * @param mixed $format
     * @param bool $applyFormat
     * @param array $valueFailed
     * @return bool|array
     * @throws \Exception
     */
    protected function checkValue($data, $format, $applyFormat = false, &$valueFailed = null) {
        if (is_string($format)) {
            $valid = $this->checkValueStringFormat($data, $format, $applyFormat, $failed);

            if (!$valid) $valueFailed = $failed;

        } else if (is_null($data)) {
            $valid = $this->getNull();
        } else if (is_array($format)) {
            $a = new ArrayS($data, $this->getNull());
            $a->setCountStrict($this->countStrict);
            $a->setFormat($format);
            $valid = $a->check(null, $arrayFailed);

            if (!$valid) $valueFailed = $arrayFailed;

        } else {
            $valid = true;
        }

        return $valid;
    }

    /**
     * @param mixed $data
     * @param string $format
     * @param bool|false $applyFormat
     * @param $failed
     * @return bool|array
     * @throws \Exception
     */
    protected function checkValueStringFormat($data, $format, $applyFormat = false, &$failed) {
        if (!isset(ArrayS::$compiledFormats[$format])) {
            $compiledString = $this->compileString($format);
            ArrayS::$compiledFormats[$format] = $compiledString;
        } else $compiledString = ArrayS::$compiledFormats[$format];

        if ($applyFormat) {
            return call_user_func($compiledString["format"], $data, $this->getNull());
        } else {
            $check = $compiledString["check"];
            return $check($data, $this->getNull(), $failed);
        }
    }

    /**
     * Returns an array of closures that can check and format
     * to the $string type
     *
     * @param $string
     * @return array
     * @throws \Exception
     */
    function compileString($string) {
        /**
         * Examples:
         *  numeric[-10, 30)
         *  integer(0, inf)
         *  float[0,1]
         */
        $numeric = '/^(numeric|float|integer|int)((\(|\[).+,[^\]\)]+(\)|\]))$/';
        /**
         * Examples:
         *  string{pendent, payed, ready, cancelled}
         *  integer{10, 100, 1000}
         *  scalar{0, false}
         *  {0, false} //same as the previous one
         */
        $valueSetScalar = '/^(scalar|string|float|integer|int|str|boolean|bool|numeric)?(\{[^}]*\})$/';

        /**
         * Examples:
         *  string(5..10)
         *  string(5..)
         *  string(..10)
         *  string(5..5) // strict length
         *  string(..) // pointless, but valid
         */
        $lengthString = '/^string(\(\d*\.\.\d*\))$/';

        $identityStructure = array(
            "check" => function($data, $null, &$failed) { return true; },
            "format" => function($data) { return $data; }
        );

        // allow "type1|type2|..."
        $types = explode("|", $string);

        /** @var Structure[] $objects */
        $objects = array();

        // Use a do-while to check always $type[0]
        $i = 0;
        do {
            $type = $types[$i];
            if (preg_match($numeric, $type, $matches)) {
                switch ($type[0]) {
                    case "n": $structure = new NumericS(); break;
                    case "f": $structure = new FloatS(); break;
                    case "i": $structure = new IntegerS(); break;
                }
                /** @var NumericS $structure */
                $structure->setRange($matches[2]);
            } else if (preg_match($valueSetScalar, $type, $matches)) {
                switch ($type[0] . $type[1]) {
                    default: //fall through to _scalar_
                    case "sc": $structure = new ScalarS(); break;
                    case "st": $structure = new StringS(); break;
                    case "nu": $structure = new NumericS(); break;
                    case "fl": $structure = new FloatS(); break;
                    case "in": $structure = new IntegerS(); break;
                    case "bo": $structure = new BooleanS(); break;
                }
                /** @var ScalarS $structure */
                $structure->setValueSet($matches[2]);
            } else if (preg_match($lengthString, $type, $matches)) {
                $structure = new StringS();
                $structure->setLength($matches[1]);
            } else {
                switch ($type) {
                    case "scalar": $structure = new ScalarS(); break;
                    case "string":// fall through to _str_
                    case "str": $structure = new StringS(); break;
                    case "numeric": $structure = new NumericS(); break;
                    case "integer":// fall through to _int_
                    case "int": $structure = new IntegerS(); break;
                    case "float": $structure = new FloatS(); break;
                    case "boolean":// fall through to _bool_
                    case "bool": $structure = new BooleanS(); break;
                    case "array":
                        $structure = new ArrayS();
                        $structure->setFormat("array");
                        $structure->setCountStrict($this->countStrict);
                        break;
                    case "*":
                    case "any":
                        // set structure:identity
                        $structure = $identityStructure;
                        break;
                    case "null":
                        // set structure:null
                        $structure = array(
                            "check" => function($data, $null, &$failed) {
                                $valid = is_null($data);
                                if (!$valid) $failed = Structure::typeof($data);
                                return $valid;
                            },
                            "format" => function() { return null; }
                        );
                        break;
                    default:
                        if (class_exists($string)) {
                            // todo: create ObjectS class
                            $structure = array(
                                "check" => function($data, $null, &$failed) use ($string) {
                                    if (is_null($data)) $valid = $null;
                                    else $valid = $data instanceof $string;

                                    if (!$valid) $failed = Structure::typeof($data);
                                    return $valid;
                                },
                                "format" => $identityStructure["format"]
                            );
                        } else {
                            // maybe $format is a simple array (type[] or type[int])
                            $structure = new ArrayS();
                            $structure->setFormat($string);
                            $structure->setCountStrict($this->countStrict);
                            $arrayStructure = true;
                        }
                        break;
                }
                //prevent errors
                if (!isset($structure)) {
                    $structure = $identityStructure;
                }
            }

            // Add the Structure to use it after the loop
            $objects[] = $structure;
        } while(count($types) > ++$i);

        /**
         * Define return functions, depending on the number of $objects
         * (1 or more)
         */
        $ret = array();
        if ($i == 1) {
            if ($objects[0] instanceof Structure) {
                $check = function ($data, $null, &$failed) use ($objects) {
                    $objects[0]->setNull($null);
                    return $objects[0]->check($data, $failed);
                };
                $format = function ($data, $null) use ($objects) {
                    $objects[0]->setNull($null);
                    return $objects[0]->format($data);
                };
            } else {
                $check = $objects[0]["check"];
                $format = function ($data) use ($objects) {
                    return call_user_func($objects[0]["format"], $data);
                };
            }
        } else {
            $check = function($data, $null, &$failed) use ($objects) {
                foreach ($objects as $obj) {
                    if ($obj instanceof Structure) {
                        $obj->setNull($null);
                        if ($obj->check($data, $failed)) return true;
                    } else if (is_array($obj) && isset($obj["check"])) {
                        $check = $obj["check"];
                        if ($check($data, $null, $failed)) return true;
                    }
                }
                return false;
            };
            $format = function($data, $null) use ($objects) {
                foreach ($objects as $obj) {
                    try {
                        if ($obj instanceof Structure) {
                            $obj->setNull($null);
                            return $obj->format($data);
                        } else {
                            return call_user_func($obj["format"], $data);
                        }
                    } catch (\Exception $e) {}
                }
                throw new \Exception("Unable to format \$data");
            };
        }
        $ret["check"] = $check;
        $ret["format"] = $format;

        if (isset($arrayStructure) && $arrayStructure) {
            $ret["meta"] = "array";
        }

        return $ret;
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
                if (!isset($this->data[$key])) {
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