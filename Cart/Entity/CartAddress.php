<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Cart\Model\CartAddressInterface;

/**
 * Class CartAddress
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddress extends AbstractAddress implements CartAddressInterface
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
