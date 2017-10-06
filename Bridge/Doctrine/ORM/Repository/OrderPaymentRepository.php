<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;

/**
 * Class OrderPaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderPaymentInterface|null findOneByKey($key)
 */
class OrderPaymentRepository extends AbstractPaymentRepository implements OrderPaymentRepositoryInterface
{

}
