<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Helper;

use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class SaleItemHelper
 * @package Ekyna\Component\Commerce\Common\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemHelper
{
    protected SubjectHelperInterface   $subjectHelper;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(SubjectHelperInterface $subjectHelper, EventDispatcherInterface $eventDispatcher)
    {
        $this->subjectHelper = $subjectHelper;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Initializes the sale item with its subject.
     */
    public function initialize(SaleItemInterface $item, ?SubjectInterface $subject): SaleItemEvent
    {
        if (null !== $subject) {
            $this->subjectHelper->assign($item, $subject);
        }

        $this->assertAssignedSubject($item);

        $event = new SaleItemEvent($item);

        $this->eventDispatcher->dispatch($event, SaleItemEvents::INITIALIZE);

        return $event;
    }

    /**
     * Builds the sale item (with its subject).
     */
    public function build(SaleItemInterface $item): SaleItemEvent
    {
        $this->assertAssignedSubject($item);

        $event = new SaleItemEvent($item);

        $this->eventDispatcher->dispatch($event, SaleItemEvents::BUILD);

        return $event;
    }

    protected function assertAssignedSubject(SaleItemInterface $item): void
    {
        if ($item->getSubjectIdentity()->hasIdentity()) {
            return;
        }

        throw new LogicException('No subject assigned.');
    }
}
