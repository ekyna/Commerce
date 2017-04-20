<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Repository;

use Ekyna\Component\Commerce\Payment\Model\PaymentTermInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface PaymentTermRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentTermRepositoryInterface extends TranslatableRepositoryInterface
{
    /**
     * Returns the longest payment term.
     *
     * @return PaymentTermInterface|null
     */
    public function findLongest(): ?PaymentTermInterface;
}
