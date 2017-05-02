<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Common\Model\MethodInterface;

/**
 * Interface PaymentMethodInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentMethodInterface extends MethodInterface
{
    /**
     * Returns whether or not the method requires manual management of payments state.
     *
     * @return bool
     */
    public function isManual();

    /**
     * Returns whether or not the method results in an customer credit balance payment.
     *
     * @return bool
     */
    public function isCredit();

    /**
     * Returns whether or not the method results in an customer outstanding balance payment.
     *
     * @return bool
     */
    public function isOutstanding();
}
