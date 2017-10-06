<?php

namespace Ekyna\Component\Commerce\Payment\Repository;

use Ekyna\Component\Commerce\Payment\Model\PaymentTermInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface PaymentTermRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentTermRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the longest payment term.
     *
     * @return PaymentTermInterface|null
     */
    public function findLongest();
}
