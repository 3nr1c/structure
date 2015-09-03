<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Enric Florit
 * @since 0.2.0
 * @date 14/7/15
 */

namespace Structure;

/**
 * Class BooleanS
 * @package Structure
 */
class BooleanS extends ScalarS {
    /**
     * @param mixed $data
     * @param bool $null
     */
    public function __construct($data = null, $null = false) {
        parent::__construct($data, $null);
        $this->setType("boolean");
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function format($data = null) {
        if (is_string($data)) {
            // All strings (except "") evaluate true with boolval
            return $data === "true";
        } else if (function_exists("boolval")) {
            return boolval($data);
        } else {
            return !!$data;
        }
    }
}