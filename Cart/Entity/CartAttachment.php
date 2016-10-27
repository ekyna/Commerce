<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Model\CartAttachmentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;

/**
 * Class CartAttachment
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAttachment extends AbstractAttachment implements CartAttachmentInterface
{
    /**
     * @var CartInterface
     */
    protected $cart;


    /**
     * @inheritdoc
     */
    public function getSale()
    {
        return $this->getCart();
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        if (null !== $sale && !$sale instanceof CartInterface) {
            throw new InvalidArgumentException('Expected instance of CartInterface');
        }

        $this->setCart($sale);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @inheritdoc
     */
    public function setCart(CartInterface $cart = null)
    {
        if (null !== $this->cart && $this->cart != $cart) {
            $this->cart->removeAttachment($this);
        }

        $this->cart = $cart;

        return $this;
    }
}
