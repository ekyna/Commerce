<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class CartPaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CartPaymentInterface|null findOneByKey(string $key)
 */
class CartPaymentRepository extends AbstractPaymentRepository implements CartPaymentRepositoryInterface
{
    /**
     * @return void
     */
    public function createNew()
    {
        throw new RuntimeException("Disabled: use payment factory.");
    }
}
