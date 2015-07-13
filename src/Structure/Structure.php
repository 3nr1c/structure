<?php
/**
 * @author Enric Florit
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
     * @param bool $null
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
     * @return bool
     */
    public function getNull() {
        return $this->null;
    }

    /**
     * @param bool $null
     */
    public function setNull($null) {
        $this->null = $null;
    }

    abstract public function checkType();
}