<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Helper;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;

/**
 * Class FlagHelper
 * @package Ekyna\Component\Commerce\Customer\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FlagHelper
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly string                   $defaultCountry,
    ) {
    }

    public function isProspect(CustomerInterface $customer): bool
    {
        return !$this->orderRepository->existsForCustomer($customer);
    }

    public function isInternational(CustomerInterface $customer): ?bool
    {
        $address = $customer->getDefaultInvoiceAddress(true);

        if (null === $address) {
            return null;
        }

        return $address->getCountry()->getCode() !== $this->defaultCountry;
    }
}
