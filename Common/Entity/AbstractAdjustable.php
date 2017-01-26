<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustableTrait;

/**
 * Class AbstractAdjustable
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAdjustable implements AdjustableInterface
{
    use AdjustableTrait;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeAdjustments();
    }
}
