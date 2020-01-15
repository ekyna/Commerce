<?php

namespace Ekyna\Component\Commerce\Payment\Repository;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface PaymentMethodRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentMethodRepositoryInterface extends TranslatableResourceRepositoryInterface
{
    /**
     * Create a new payment method with pre-populated messages (one by notifiable state).
     *
     * @return PaymentMethodInterface
     */
    public function createNew();

    /**
     * Finds the available and enabled payment methods.
     *
     * @param CurrencyInterface $currency Filter authorized currency.
     *
     * @return PaymentMethodInterface[]
     */
    public function findAvailable(CurrencyInterface $currency = null): array;

    /**
     * Finds the enabled payment methods.
     *
     * @param CurrencyInterface $currency Filter authorized currency.
     *
     * @return PaymentMethodInterface[]
     */
    public function findEnabled(CurrencyInterface $currency = null): array;

    /**
     * Finds payment methods by their factory name.
     *
     * @param string $name
     * @param bool   $available
     *
     * @return PaymentMethodInterface[]
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
