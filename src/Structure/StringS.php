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
 * Class StringS
 * @package Structure
 */
class StringS extends ScalarS {
    protected $minLength = 0;
    protected $maxLength = 0;

    protected static $compiledLengths = array();

    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("string");
    }

    /**
     * Sets a min and max length for $data.
     *
     * @var int $min
     * @var int $max
     *
     * @var string $length Length set with the format [min;max].
     *
     * @throws \Exception
     * @return bool
     */
    public function setLength() {
        $argc = func_num_args();
        $argv = func_get_args();

        if ($argc == 1 && is_integer($argv[0])) {
            $this->minLength = $argv[0];
            $this->maxLength = 0;
            return true;
        } else if ($argc == 2 && is_integer($argv[0]) && is_integer($argv[1])) {
            if ($argv[0] > $argv[1]) {
                throw new \Exception("Min length must be less or equal to max length");
            }

            $this->minLength = $argv[0];
            $this->maxLength = $argv[1];

            return true;
        } else if ($argc == 1 && is_string($argv[0])) {
            $str = $argv[0];

            if (isset(StringS::$compiledLengths[$str])) {
                $lengthInfo = StringS::$compiledLengths[$str];
                goto set_info;
            }

            $lengthInfo = array();

            for ($i = 0; $i < strlen($str); $i++) {
                if (isset($matchedBracket) && $matchedBracket === true) {
                    throw new \Exception("Unexpected character '" . $str[$i] . "'");
                } else if ($i === 0 && $str[$i] === "(") {
                    if (count($lengthInfo) === 0) {
                        $lengthInfo[] = "";
                        $matchedBracket = false;
                    } else {
                        throw new \Exception("Unexpected character '('");
                    }
                } else if ($str[$i] === ")") {
                    $matchedBracket = true;
                } else if (is_numeric($str[$i]) && count($lengthInfo) > 0) {
                    if (count($lengthInfo) === 1 || count($lengthInfo) === 2) {
                        $lengthInfo[count($lengthInfo) - 1] .= $str[$i];
                    } else {
                        throw new \Exception("Unexpected character '" . $str[$i] . "'");
                    }
                } else if ($str[$i] === '.' && $i + 1 < strlen($str) && $str[$i + 1] === '.') {
                    if (count($lengthInfo) === 1) {
                        $lengthInfo[] = "";
                        $i++;
                    } else {
                        throw new \Exception("Unexpected token '..'");
                    }
                } else {
                    throw new \Exception("Unexpected character '" . $str[$i] . "'");
                }
            }

            $lengthInfo[0] = empty($lengthInfo[0]) ? 0 : intval($lengthInfo[0]);
            $lengthInfo[1] = empty($lengthInfo[1]) ? 0 : intval($lengthInfo[1]);

            if ($lengthInfo[1] !== 0 && $lengthInfo[0] !== 0 && $lengthInfo[0] > $lengthInfo[1]) {
                throw new \Exception("Min length must be less than or equal to max length");
            }
            StringS::$compiledLengths[$str] = $lengthInfo;

set_info:
            $this->minLength = $lengthInfo[0];
            $this->maxLength = $lengthInfo[1];
            return true;
        }
        return false;
    }

    /**
     * @param string $data
     * @param $format
     * @return bool
     */
    protected function checkLength($data = null, &$format) {
        if ($data === null) $data = $this->getData();

        $valid = $this->minLength <= 0 || strlen($data) >= $this->minLength;
        $valid = $valid && ($this->maxLength <= 0 || strlen($data) <= $this->maxLength);

        if (!$valid) $format = "string:length";

        return $valid;
    }

    /**
     * @param mixed $data
     * @param $failed
     * @return bool
     */
    public function check($data = null, &$failed = null) {
        $valid = parent::check($data, $failed) && $this->checkLength($data, $failed);

        if (!$valid) Structure::$lastFail = $failed;
        return $valid;
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