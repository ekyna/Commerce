<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model\CartAttachmentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class CartAttachment
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAttachment extends AbstractAttachment implements CartAttachmentInterface
{
    protected ?CartInterface $cart = null;


    /**
     * @return CartInterface|null
     */
    public function getSale(): ?SaleInterface
    {
        return $this->getCart();
    }

    public function setSale(?SaleInterface $sale): SaleAttachmentInterface
    {
        if ($sale && !$sale instanceof CartInterface) {
            throw new UnexpectedTypeException($sale, CartInterface::class);
        }

        $this->setCart($sale);

        return $this;
    }

    public function getCart(): ?CartInterface
    {
        return $this->cart;
    }

    public function setCart(?CartInterface $cart): CartAttachmentInterface
    {
        if ($cart === $this->cart) {
            return $this;
        }

        if ($previous = $this->cart) {
            $this->cart = null;
            $previous->removeAttachment($this);
        }

        if ($this->cart = $cart) {
            $this->cart->addAttachment($this);
        }

        return $this;
    }
}
