<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface SupplierPaymentInterface
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SupplierPaymentInterface
    extends ResourceInterface,
            ExchangeSubjectInterface,
            StateSubjectInterface,
            TimestampableInterface
{
    public function getOrder(): ?SupplierOrderInterface;

    public function setOrder(?SupplierOrderInterface $order): SupplierPaymentInterface;

    public function getAmount(): Decimal;

    public function setAmount(Decimal $amount): SupplierPaymentInterface;

    public function isToForwarder(): bool;

    public function setToForwarder(bool $toForwarder): SupplierPaymentInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): SupplierPaymentInterface;
}
