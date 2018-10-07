<?php

declare(strict_types=1);

/**
 * Christophe Avonture (based on work of Benjamin Eberlei)
 * Written date : 2018-09-13
 *
 * Description
 * Enumeration helper
 *
 * @link https://beberlei.de/2009/08/31/enums-in-php.html
 */

namespace cavo789\Helpers;

abstract class Enum
{
    /**
     * Constructor
     *
     * @suppress PhanUndeclaredProperty
     *
     * @param mixed $value
     */
    final public function __construct($value)
    {
        $c = new \ReflectionClass($this);
        if (!in_array($value, $c->getConstants())) {
            throw new \InvalidArgumentException($value . ' isn\'t..');
        }
        $this->value = $value;
    }

    /**
     * Undocumented function
     *
     * @suppress PhanUndeclaredProperty
     *
     * @return string
     */
    final public function __toString() : string
    {
        return $this->value;
    }
}
