<?php

namespace Ekyna\Component\Commerce\Customer\Updater;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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
    public function updateOutstandingBalance(CustomerInterface $customer, $amount, $relative = false)
    {
        $balance = $customer->getOutstandingBalance();

        $delta = $amount;
        if (!$relative) {
            $delta -= $balance;
        }

        if (0 < $delta) {
            // Credit case
            $limit = $customer->getOutstandingLimit();
            if ($limit < $balance + $delta) {
                $delta = $limit - $balance;
            }
        } elseif (0 > $delta) {
            // Debit case
            if (0 > $balance + $delta) {
                $delta = -$balance;
            }
        }
        if (0 == $delta) {
            return 0;
        }

        $balance = $balance + $delta;
        if (0 > $balance) {
            throw new InvalidArgumentException("Unexpected outstanding amount.");
        }

        $customer->setOutstandingBalance($balance);
        $this->persistenceHelper->persistAndRecompute($customer);

        return $relative ? $delta : $balance;
    }
}
