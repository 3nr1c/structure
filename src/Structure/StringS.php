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

    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("string");
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