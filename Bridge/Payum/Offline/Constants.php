<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Offline;

/**
 * Class Constants
 * @package Ekyna\Component\Commerce\Bridge\Payum\Offline
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class Constants
{
    const FACTORY_NAME = 'offline';

    const FIELD_STATUS = 'status';

    const STATUS_PENDING  = 'pending';
    const STATUS_CAPTURED = 'captured';
    const STATUS_REFUND   = 'refund';
    const STATUS_CANCELED = 'canceled';

    final private function __construct()
    {
    }
}
