<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance;

/**
 * Class Constants
 * @package Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class Constants
{
    const FACTORY_NAME = 'outstanding_balance';

    const FIELD_BALANCE = 'balance';

    const FIELD_LIMIT = 'limit';

    const FIELD_AMOUNT = 'amount';

    const FIELD_STATUS = 'status';

    const STATUS_CAPTURED = 'captured';

    const STATUS_AUTHORIZED = 'authorized';

    const STATUS_FAILED = 'failed';

    const STATUS_CANCELLED = 'cancelled';
}
