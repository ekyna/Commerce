<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class AbstractPaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPaymentRepository extends ResourceRepository implements PaymentRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByKey($key)
    {
        return $this->findOneBy(['key' => $key]);
    }
}
