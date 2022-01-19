<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Factory;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SaleFactoryInterface
 * @package Ekyna\Component\Commerce\Common\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SaleFactoryInterface extends ResourceFactoryInterface
{
    /**
     * @return SaleInterface
     */
    public function create(bool $initialize = true): ResourceInterface;

    public function createWithCustomer(CustomerInterface $customer): SaleInterface;

    public function initialize(SaleInterface $sale): void;
}
