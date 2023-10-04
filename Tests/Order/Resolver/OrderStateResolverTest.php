<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Order\Resolver;

/**
 * Class OrderStateResolverTest
 * @package Ekyna\Component\Commerce\Tests\Order\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderStateResolverTest
{
    public function testResolveState(): void
    {
        // TODO Test refund/canceled cancellation
        //  -> payment, shipment and invoice states are set to 'canceled' if needed
    }
}
