<?php

namespace Ekyna\Component\Commerce\Common\Resolver;

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
     * @inheritDoc
     */
    public function resolve($subject)
    {
        $changed = false;

        if ($subject instanceof PaymentSubjectInterface) {
            $changed |= $this->paymentStateResolver->resolve($subject);
        }

        if ($subject instanceof ShipmentSubjectInterface) {
            $changed |= $this->shipmentStateResolver->resolve($subject);
        }

        return $changed;
    }
}
