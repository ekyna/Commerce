<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Class AbstractSaleStateResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleStateResolver extends AbstractStateResolver
{
    protected StateResolverInterface $paymentStateResolver;
    protected StateResolverInterface $shipmentStateResolver;
    protected StateResolverInterface $invoiceStateResolver;

    public function setPaymentStateResolver(StateResolverInterface $resolver): void
    {
        $this->paymentStateResolver = $resolver;
    }

    public function setShipmentStateResolver(StateResolverInterface $resolver): void
    {
        $this->shipmentStateResolver = $resolver;
    }

    public function setInvoiceStateResolver(StateResolverInterface $resolver): void
    {
        $this->invoiceStateResolver = $resolver;
    }

    /**
     * @inheritDoc
     *
     * @param SaleInterface $subject
     */
    public function resolve(object $subject): bool
    {
        $this->supports($subject);

        $changed = false;

        if ($subject instanceof PaymentSubjectInterface) {
            if ($subject->isSample()) {
                if ($subject->getPaymentState() !== PaymentStates::STATE_COMPLETED) {
                    $subject->setPaymentState(PaymentStates::STATE_COMPLETED);

                    $changed = true;
                }
            } else {
                $changed = $this->paymentStateResolver->resolve($subject);
            }
        }

        if ($subject instanceof ShipmentSubjectInterface) {
            $changed = $this->shipmentStateResolver->resolve($subject) || $changed;
        }

        if ($subject instanceof InvoiceSubjectInterface) {
            if ($subject->isSample()) {
                if ($subject->getInvoiceState() !== InvoiceStates::STATE_COMPLETED) {
                    $subject->setInvoiceState(InvoiceStates::STATE_COMPLETED);

                    $changed = true;
                }
            } else {
                $changed = $this->invoiceStateResolver->resolve($subject) || $changed;
            }
        }

        $state = $this->resolveState($subject);

        if ($state !== $subject->getState()) {
            $subject->setState($state);

            $this->postStateResolution($subject);

            return true;
        }

        return $changed;
    }

    /**
     * Post state resolution (called if state changed).
     */
    protected function postStateResolution(SaleInterface $sale): void
    {

    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof SaleInterface) {
            throw new UnexpectedTypeException($subject, SaleInterface::class);
        }
    }
}
