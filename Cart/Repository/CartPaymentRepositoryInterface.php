<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;

/**
 * Interface CartPaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Cart\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements PaymentRepositoryInterface<CartPaymentInterface>
 */
interface CartPaymentRepositoryInterface extends PaymentRepositoryInterface
{

}
