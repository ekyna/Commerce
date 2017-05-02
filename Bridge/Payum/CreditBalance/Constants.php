<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\CreditBalance;

/**
 * Class Constants
 * @package Ekyna\Component\Commerce\Bridge\Payum\CustomerBalance
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class Constants
{
    const FACTORY_NAME = 'credit_balance';

    const FIELD_BALANCE = 'balance';

    const FIELD_AMOUNT = 'amount';

    const FIELD_STATUS = 'status';

    const STATUS_CAPTURED = 'captured';

    const STATUS_AUTHORIZED = 'authorized';

    const STATUS_FAILED = 'failed';

    const STATUS_CANCELLED = 'cancelled';
}
