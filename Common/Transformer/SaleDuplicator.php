<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Model\InChargeSubjectInterface;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvents;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class SaleDuplicator
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleDuplicator extends AbstractOperator implements SaleDuplicatorInterface
{
    public function initialize(SaleInterface $source, SaleInterface $target): ResourceEventInterface
    {
        $this->source = $source;
        $this->target = $target;

        $event = new SaleTransformEvent($this->source, $this->target);

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::INIT_DUPLICATE);
        if ($event->isPropagationStopped()) {
            return $event;
        }

        // Copies source to target
        $this
            ->saleCopierFactory
            ->create($this->source, $this->target)
            ->copyData()
            ->copyItems();

        $this->target
            ->setCustomerGroup(null)
            ->setShipmentMethod(null)
            ->setPaymentMethod(null)
            ->setPaymentTerm(null)
            ->setOutstandingLimit(new Decimal(0))
            ->setDepositTotal(new Decimal(0))
            ->setSource(SaleSources::SOURCE_COMMERCIAL)
            ->setExchangeRate(null)
            ->setExchangeDate(null)
            ->setAcceptedAt(null);

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::POST_COPY);

        $this->getFactory($this->target)->initialize($this->target);

        // Clear addresses
        $this->target
            ->setSameAddress(true)
            ->setInvoiceAddress(null)
            ->setDeliveryAddress(null);

        if ($this->target instanceof InChargeSubjectInterface) {
            $this->target->setInCharge(null);
        }

        return $event;
    }

    public function duplicate(): ?ResourceEventInterface
    {
        if (null === $this->source || null === $this->target) {
            throw new LogicException('Please call initialize first.');
        }

        $event = new SaleTransformEvent($this->source, $this->target);

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::PRE_DUPLICATE);
        if ($event->hasErrors() || $event->isPropagationStopped()) {
            return $event;
        }

        // Persist the target sale
        $targetEvent = $this->getManager($this->target)->save($this->target);
        if ($targetEvent->hasErrors() || $targetEvent->isPropagationStopped()) {
            return $targetEvent;
        }

        $this->getManager($this->target)->refresh($this->target);

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::POST_DUPLICATE);
        if ($event->hasErrors() || $event->isPropagationStopped()) {
            return $event;
        }

        // Unset source and target sales
        $this->source = null;
        $this->target = null;

        return null;
    }
}
