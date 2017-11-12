<?php

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Class AbstractSaleStateResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleStateResolver implements StateResolverInterface
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
     */
    public function resolve($subject)
    {
        if (!$subject instanceof SaleInterface) {
            throw new InvalidArgumentException("Expected instance of " . SaleInterface::class);
        }

        $changed = false;

        if ($subject instanceof PaymentSubjectInterface) {
            $changed |= $this->paymentStateResolver->resolve($subject);
        }

        if ($subject instanceof ShipmentSubjectInterface) {
            $changed |= $this->shipmentStateResolver->resolve($subject);
        }

        if ($subject instanceof InvoiceSubjectInterface) {
            $changed |= $this->invoiceStateResolver->resolve($subject);
        }

        $state = $this->resolveState($subject);

        if ($state !== $subject->getState()) {
            $subject->setState($state);

            return true;
        }

        return $changed;
    }

    /**
     * Resolves the sale state.
     *
     * @param SaleInterface $sale
     *
     * @return string
     */
    abstract protected function resolveState(SaleInterface $sale);
}
