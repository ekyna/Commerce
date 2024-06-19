<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\EventListener;

use DateTime;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

use function is_null;

/**
 * Class QuoteEventSubscriber
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteListener extends AbstractSaleListener
{
    protected function handleInsert(SaleInterface $sale): bool
    {
        $changed = parent::handleInsert($sale);

        /** @var QuoteInterface $sale */

        $changed = $this->handleProject($sale) || $changed;

        return $this->handleEditable($sale) || $changed;
    }

    protected function handleUpdate(SaleInterface $sale): bool
    {
        $changed = parent::handleUpdate($sale);

        /** @var QuoteInterface $sale */

        $changed = $this->handleProject($sale) || $changed;

        return $this->handleEditable($sale) || $changed;
    }

    protected function handleProject(QuoteInterface $quote): bool
    {
        if (!is_null($quote->getProjectDate())) {
            return false;
        }

        if (!is_null($quote->getProjectTrust())) {
            return false;
        }

        if (is_null($quote->getProjectAlive())) {
            return false;
        }

        $quote->setProjectAlive(null);

        return true;
    }

    protected function handleEditable(QuoteInterface $quote): bool
    {
        if (!$quote->isEditable()) {
            return false;
        }

        $changed = false;

        if (!$quote->isAutoDiscount()) {
            $quote->setAutoDiscount(true);
            $changed = true;
        }

        if (!$quote->isAutoShipping()) {
            $quote->setAutoShipping(true);
            $changed = true;
        }

        if (!$quote->isAutoNotify()) {
            $quote->setAutoNotify(true);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @TODO Use common sale state constants. Move into parent::updateState() method.
     */
    protected function updateState(SaleInterface $sale): bool
    {
        if (!parent::updateState($sale)) {
            return false;
        }

        if ($sale->getState() !== QuoteStates::STATE_ACCEPTED) {
            $sale->setAcceptedAt(null);

            return true;
        }

        if (null === $sale->getAcceptedAt()) {
            $sale->setAcceptedAt(new DateTime());
        }

        return true;
    }

    protected function getSaleFromEvent(ResourceEventInterface $event): SaleInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof QuoteInterface) {
            throw new UnexpectedTypeException($resource, QuoteInterface::class);
        }

        return $resource;
    }

    protected function scheduleContentChangeEvent(SaleInterface $sale): void
    {
        if (!$sale instanceof QuoteInterface) {
            throw new UnexpectedTypeException($sale, QuoteInterface::class);
        }

        $this->persistenceHelper->scheduleEvent($sale, QuoteEvents::CONTENT_CHANGE);
    }

    protected function scheduleStateChangeEvent(SaleInterface $sale): void
    {
        if (!$sale instanceof QuoteInterface) {
            throw new UnexpectedTypeException($sale, QuoteInterface::class);
        }

        $this->persistenceHelper->scheduleEvent($sale, QuoteEvents::STATE_CHANGE);
    }
}
