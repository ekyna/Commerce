<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Order\Model\OrderAddressInterface;

/**
 * Class OrderAddress
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddress extends AbstractAddress implements OrderAddressInterface
{
    /**
     * @var int
     */
    protected $id;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }
}
