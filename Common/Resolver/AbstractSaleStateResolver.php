<?php

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
    /**
     * @var StateResolverInterface
     */
    protected $paymentStateResolver;

    /**
     * @var StateResolverInterface
     */
    protected $shipmentStateResolver;

    /**
     * @var StateResolverInterface
     */
    protected $invoiceStateResolver;


    /**
     * Sets the payment state resolver.
     *
     * @param StateResolverInterface $resolver
     */
    public function setPaymentStateResolver(StateResolverInterface $resolver)
    {
        $this->paymentStateResolver = $resolver;
    }

    /**
     * Sets the shipment state resolver.
     *
     * @param StateResolverInterface $resolver
     */
    public function setShipmentStateResolver(StateResolverInterface $resolver)
    {
        $this->shipmentStateResolver = $resolver;
    }

    /**
     * Sets the invoice state resolver.
     *
     * @param StateResolverInterface $resolver
     */
    public function setInvoiceStateResolver(StateResolverInterface $resolver)
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
                $changed |= $this->paymentStateResolver->resolve($subject);
            }
        }

        if ($subject instanceof ShipmentSubjectInterface) {
            $changed |= $this->shipmentStateResolver->resolve($subject);
        }

        if ($subject instanceof InvoiceSubjectInterface) {
            if ($subject->isSample()) {
                if ($subject->getInvoiceState() !== InvoiceStates::STATE_COMPLETED) {
                    $subject->setInvoiceState(InvoiceStates::STATE_COMPLETED);

                    $changed = true;
                }
            } else {
                $changed |= $this->invoiceStateResolver->resolve($subject);
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
     *
     * @param SaleInterface $sale
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
