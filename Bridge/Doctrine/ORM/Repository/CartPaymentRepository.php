<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartPaymentRepositoryInterface;

/**
 * Class CartPaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CartPaymentInterface|null findOneByKey($key)
 */
class CartPaymentRepository extends AbstractPaymentRepository implements CartPaymentRepositoryInterface
{

}
