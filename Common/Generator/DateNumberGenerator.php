<?php

namespace Ekyna\Component\Commerce\Common\Generator;

/**
 * Class DateNumberGenerator
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DateNumberGenerator extends DefaultNumberGenerator
{
    /**
     * Generates the number.
     *
     * @param string $number
     *
     * @return string
     */
    protected function generateNumber($number)
    {
        $datePrefix = (new \DateTime())->format($this->prefix);

        if (0 !== strpos($number, $datePrefix)) {
            $number = 0;
        } else {
            $number = intval(substr($number, strlen($datePrefix)));
        }

        return $datePrefix . str_pad($number + 1, 10 - strlen($datePrefix), '0', STR_PAD_LEFT);
    }
}
