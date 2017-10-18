<?php

namespace Ekyna\Component\Commerce\Customer\Updater;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class CustomerUpdater
 * @package Ekyna\Component\Commerce\Customer\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerUpdater implements CustomerUpdaterInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @inheritdoc
     */
    public function updateCreditBalance(CustomerInterface $customer, $amount, $relative = false)
    {
        $old = $customer->getCreditBalance();
        $new = $relative ? $old + $amount : $amount;

        if ($old != $new) {
            $customer->setCreditBalance($new);
            $this->persistenceHelper->persistAndRecompute($customer, false);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateOutstandingBalance(CustomerInterface $customer, $amount, $relative = false)
    {
        // Switch to parent if available
        if ($customer->hasParent()) {
            $customer = $customer->getParent();
        }

        $old = $customer->getOutstandingBalance();
        $new = $relative ? $old + $amount : $amount;

        if ($old != $new) {
            $customer->setOutstandingBalance($new);
            $this->persistenceHelper->persistAndRecompute($customer, false);

            return true;
        }

        return false;
    }
}
