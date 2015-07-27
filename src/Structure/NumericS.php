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


class NumericS extends ScalarS {
    protected $range = null;

    protected $lowerBound;
    protected $lowerStrict;
    protected $lowerInfinity = false;
    protected $upperBound;
    protected $upperStrict;
    protected $upperInfinity = true;

    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("numeric");
    }

    /**
     * @param string $range
     * @throws \Exception
     */
    public function setRange($range) {
        if (!is_string($range)) {
            throw new \Exception("Variable \$range must be a string");
        }
        $rangeInformation = array();

        // Parse $range
        for ($i = 0; $i < strlen($range); $i++) {
            if ($range[$i] === '[' || $range[$i] === '(') {
                if (count($rangeInformation) === 0) {
                    $rangeInformation[] = ($range[$i] === '(');
                    $rangeInformation[] = "";
                } else {
                    throw new \Exception("Unexpected character '" . $range[$i] . "'");
                }
            } else if ($range[$i] === '-' || $range[$i] === '+') {
                if (count($rangeInformation) === 2 || count($rangeInformation) === 3) {
                    if (strlen(end($rangeInformation)) === 0) {
                        $rangeInformation[count($rangeInformation) - 1] .= $range[$i];
                    } else {
                        throw new \Exception("Unexpected character " . $range[$i] . "'");
                    }
                } else {
                    throw new \Exception("Unexpected character '-'");
                }
            } else if (is_numeric($range[$i])) {
                if (count($rangeInformation) === 2 || count($rangeInformation) === 3) {
                    $rangeInformation[count($rangeInformation) - 1] .= $range[$i];
                } else {
                    throw new \Exception("Unexpected numeric character '" . $range[$i] . "");
                }
            } else if ($range[$i] === '.') {
                if (count($rangeInformation) === 2 || count($rangeInformation) === 3) {
                    $current = $rangeInformation[count($rangeInformation) - 1];
                    $current .= $range[$i];
                    if (is_numeric($current)) {
                        $rangeInformation[count($rangeInformation) - 1] = $current;
                    } else {
                        throw new \Exception("Unexpected character '.'");
                    }
                } else {
                    throw new \Exception("Unexpected character '.'");
                }
            } else if ($range[$i] === ',') {
                if (count($rangeInformation) === 2){
                    $rangeInformation[] = "";
                } else {
                    throw new \Exception("Unexpected character ','");
                }
            } else if ($range[$i] === ']' || $range[$i] === ')') {
                if (count($rangeInformation) === 3) {
                    $rangeInformation[] = ($range[$i] === ')');
                } else {
                    throw new \Exception("Unexpected character '" . $range[$i] . "'");
                }
            } else if ($range[$i] === ' ') {
                if ((count($rangeInformation) === 2 && strlen($rangeInformation[1]) > 0)
                    || (count($rangeInformation) === 3 && strlen($rangeInformation[2]) > 0)) {
                    if ($i + 1 < strlen($range)
                        && (is_numeric($range[$i + 1]) || $range[$i + 1] === "-" || $range[$i + 1] === '.')) {
                        throw new \Exception("Unexpected space character ' '");
                    }
                }
            } else if ($range[$i] === 'i' && $range[$i+1] === 'n' && $range[$i+2] === 'f') {
                if (count($rangeInformation) === 2 && $rangeInformation[1] === '-') {
                    $rangeInformation[1] = "-inf";
                } else if (count($rangeInformation) === 3
                    && ($rangeInformation[2] === '+' || $rangeInformation[2] === '')) {
                    $rangeInformation[2] = "+inf";
                } else {
                    throw new \Exception("Unexpected expression 'inf'");
                }
                $i += 2;
            } else {
                throw new \Exception("Unexpected character '" . $range[$i] . "'");
            }
        }

        $this->range = $range;

        if (count($rangeInformation) !== 4) {
            throw new \Exception("Incorrect range string format");
        } else if ((float)$rangeInformation[2] < (float)$rangeInformation[1]) {
            throw new \Exception("Upper bound must be >= (greater than or equal to) lower bound");
        }

        $this->lowerStrict = $rangeInformation[0];
        $this->lowerBound = (float)$rangeInformation[1];
        $this->lowerInfinity = ($rangeInformation[1] === "-inf");
        $this->upperBound = (float)$rangeInformation[2];
        $this->upperStrict = $rangeInformation[3];
        $this->upperInfinity = ($rangeInformation[2] === "+inf");
    }

    /**
     * @return string
     */
    public function getRange() {
        return $this->range;
    }

    /**
     * It is assumed that the $data is numeric
     * @param integer|float $data
     * @return bool
     */
    protected function checkRange($data = null) {
        if (is_null($this->range)) return true;

        if (is_null($data)) {
            $data = $this->getData();
        }

        $valid = true;

        if ($this->lowerStrict) {
            $valid = ($valid && ($this->lowerInfinity || $this->lowerBound < $data));
        } else {
            $valid = ($valid && ($this->lowerInfinity || $this->lowerBound <= $data));
        }

        if ($this->upperStrict) {
            $valid = ($valid && ($this->upperInfinity || $data < $this->upperBound));
        } else {
            $valid = ($valid && ($this->upperInfinity || $data <= $this->upperBound));
        }

        return $valid;
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function check($data = null) {
        if ($this->getNull()) {
            return (is_null($this->data) || $this->checkType($data))
                && $this->checkRange($data)
                && $this->checkValueSet($data);
        } else {
            return $this->checkType($data) && $this->checkRange($data) && $this->checkValueSet($data);
        }
    }

    /**
     * @param mixed $data
     * @return float
     * @throws \Exception
     */
    public function format($data = null) {
        $validType = $this->checkType($data);
        $validRange = $this->checkRange($data);
        $validValue = $this->checkValueSet($data);

        if ($validType && $validRange && $validValue) {
            return $data;
        } else if ($validType && (!$validRange || !$validValue)) {

            if ($this->getNull()) {
                return null;
            } else if (!$validValue) {
                return $this->getValueSet()[0];
            } else {
                throw new \Exception("Unable to format " . $this->getType() . " to range" . $this->getRange());
            }

        } else if (!$validType) {
            if (!settype($data, $this->getType())) {
                if ($this->getType() === "integer" && (float)$data !== (int)$data) {
                    $data = (float)$data;
                } else {
                    $data = (int)$data;
                }
            }
            $validRange = $this->checkRange($data);
            $validValue = $this->checkValueSet($data);

            if ($validRange && $validValue) {
                return $data;
            } else if ($this->getNull()) {
                return null;
            } else if (!$validValue) {
                return $this->getValueSet()[0];
            } else {
                throw new \Exception("Unable to format " . $this->getType() . " to range" . $this->getRange());
            }
        }
    }
}