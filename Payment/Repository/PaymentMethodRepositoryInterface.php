<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Repository;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface PaymentMethodRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<PaymentMethodInterface>
 */
interface PaymentMethodRepositoryInterface extends TranslatableRepositoryInterface
{
    /**
     * Finds the available and enabled payment methods.
     *
     * @param CurrencyInterface|null $currency Filter authorized currency.
     *
     * @return array<PaymentMethodInterface>
     */
    public function findAvailable(CurrencyInterface $currency = null): array;

    /**
     * Finds the enabled payment methods.
     *
     * @param CurrencyInterface|null $currency Filter authorized currency.
     *
     * @return array<PaymentMethodInterface>
     */
    public function findEnabled(CurrencyInterface $currency = null): array;

    /**
     * Finds payment methods by their factory name.
     *
     * @param string $name
     * @param bool   $available
     *
     * @return array<PaymentMethodInterface>
     */
    public function findByFactoryName(string $name, bool $available = true): array;

    /**
     * Finds a payment method by its factory name.
     *
     * @param string $name
     * @param bool   $available
     *
     * @return PaymentMethodInterface|null
     */
    public function findOneByFactoryName(string $name, bool $available = true): ?PaymentMethodInterface;
}
