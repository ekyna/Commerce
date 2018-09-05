<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Request;

use Payum\Core\Request\Generic;

/**
 * Class FraudLevel
 * @package Ekyna\Component\Commerce\Bridge\Payum\Request
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FraudLevel extends Generic
{
    /**
     * @var int
     */
    private $level = 0;


    /**
     * Returns the level.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Sets the level.
     *
     * @param int $level
     *
     * @return FraudLevel
     */
    public function setLevel(int $level)
    {
        $this->level = $level;

        return $this;
    }
}
