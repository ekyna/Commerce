<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Message;

/**
 * Class UpdateOrderMargin
 * @package Ekyna\Component\Commerce\Order\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateOrderMargin
{
    public function __construct(
        public int $orderId
    ) {
    }
}
