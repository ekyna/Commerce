<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectTrait;
use Ekyna\Component\Commerce\Common\Model\StateSubjectTrait;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierPaymentInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

use function sprintf;

/**
 * Class SupplierPayment
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierPayment extends AbstractResource implements SupplierPaymentInterface
{
    use ExchangeSubjectTrait;
    use StateSubjectTrait;
    use TimestampableTrait;

    private ?SupplierOrderInterface $order       = null;
    private Decimal                 $amount;
    private bool                    $toForwarder = false;
    private ?string                 $description = null;

    public function __construct()
    {
        $this->amount = new Decimal(0);
        $this->state = PaymentStates::STATE_CAPTURED;

        $this->initializeTimestampable();
    }

    public function __toString(): string
    {
        if ($this->order) {
            return sprintf(
                '[%s] %s',
                $this->order->getNumber(),
                $this->getCreatedAt()->format('Y-m-d')
            );
        }

        return 'New supplier payment';
    }

    public function getOrder(): ?SupplierOrderInterface
    {
        return $this->order;
    }

    public function setOrder(?SupplierOrderInterface $order): SupplierPaymentInterface
    {
        if ($this->order === $order) {
            return $this;
        }

        if ($current = $this->order) {
            $this->order = null;
            $current->removePayment($this);
        }

        if ($this->order = $order) {
            $order->addPayment($this);
        }

        return $this;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function setAmount(Decimal $amount): SupplierPaymentInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function isToForwarder(): bool
    {
        return $this->toForwarder;
    }

    public function setToForwarder(bool $toForwarder): SupplierPaymentInterface
    {
        $this->toForwarder = $toForwarder;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): SupplierPaymentInterface
    {
        $this->description = $description;

        return $this;
    }
}
