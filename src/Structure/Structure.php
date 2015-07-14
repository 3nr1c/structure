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


abstract class Structure {
    protected $data;
    protected $type;
    protected $null;

    /**
     * @param string $type
     * @param mixed $data
     * @param boolean $null
     */
    public function __construct($type = "", $data = null, $null = false) {
        $this->setType($type);
        $this->setData($data);
        $this->setNull($null);
    }

    /**
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data = null) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return boolean
     */
    public function getNull() {
        return $this->null;
    }

    /**
     * @param boolean $null
     */
    public function setNull($null) {
        $this->null = $null;
    }

    /**
     * @param mixed $data
     * @return boolean
     */
    abstract protected function checkType($data = null);

    /**
     * @param mixed $data
     * @return boolean
     */
    abstract public function check($data = null);

    /**
     * @param mixed $data
     * @return mixed
     */
    abstract public function format($data = null);

    /**
     * @param mixed $format
     * @param null $data
     * @param boolean $countStrict
     * @param boolean $null
     * @return ArrayS
     * @throws \Exception
     */
    public static function ArrayS($format = null, $data = null, $countStrict = true, $null = false) {
        $array = new ArrayS();
        $array->setFormat($format);
        $array->setData($data = null);
        $array->setCountStrict($countStrict);
        $array->setNull($null);
        return $array;
    }

    /**
     * @param string $range
     * @param mixed $data
     * @param bool $null
     * @return NumericS
     * @throws \Exception
     */
    public static function NumericS($range = null, $data = null, $null = false) {
        $numeric = new NumericS();
        $numeric->setRange($range);
        $numeric->setData($data);
        $numeric->setNull($null);
        return $numeric;
    }

    /**
     * @param string $range
     * @param mixed $data
     * @param bool $null
     * @return IntegerS
     * @throws \Exception
     */
    public static function IntegerS($range = null, $data = null, $null = false) {
        $numeric = new IntegerS();
        $numeric->setRange($range);
        $numeric->setData($data);
        $numeric->setNull($null);
        return $numeric;
    }

    /**
     * @param string $range
     * @param mixed $data
     * @param bool $null
     * @return FloatS
     * @throws \Exception
     */
    public static function FloatS($range = null, $data = null, $null = false) {
        $numeric = new FloatS();
        $numeric->setRange($range);
        $numeric->setData($data);
        $numeric->setNull($null);
        return $numeric;
    }
}