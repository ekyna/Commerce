<?php

namespace Ekyna\Component\Commerce\Accounting\EventListener;

use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class AccountingListener
 * @package Ekyna\Component\Commerce\Accounting\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AccountingListener
{
    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $accounting = $this->getAccountingFromEvent($event);

        $this->buildName($accounting);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $accounting = $this->getAccountingFromEvent($event);

        $this->buildName($accounting);
    }

    /**
     * Returns the accounting from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return AccountingInterface
     * @throws InvalidArgumentException
     */
    protected function getAccountingFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof AccountingInterface) {
            throw new InvalidArgumentException('Expected instance of ' . AccountingInterface::class);
        }

        return $resource;
    }

    /**
     * Builds the accounting name.
     *
     * @param AccountingInterface $accounting
     */
    abstract protected function buildName(AccountingInterface $accounting);
}
