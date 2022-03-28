<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;

/**
 * Class AvailabilityResolverFactory
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AvailabilityResolverFactory
{
    private InvoiceSubjectCalculatorInterface $invoiceCalculator;
    private ShipmentSubjectCalculatorInterface $shipmentCalculator;
    /** @var array<AvailabilityResolver>  */
    private array $resolvers;

    public function __construct(
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        ShipmentSubjectCalculatorInterface $shipmentCalculator
    ) {
        $this->invoiceCalculator = $invoiceCalculator;
        $this->shipmentCalculator = $shipmentCalculator;

        $this->clear();
    }

    public function create(): AvailabilityResolver
    {
        return new AvailabilityResolver(
            $this->invoiceCalculator,
            $this->shipmentCalculator,
            null
        );
    }

    public function createWithInvoice(InvoiceInterface $invoice): AvailabilityResolver
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->getInvoice() === $invoice) {
                return $resolver;
            }
        }

        return $this->resolvers[] = new AvailabilityResolver(
            $this->invoiceCalculator,
            $this->shipmentCalculator,
            $invoice
        );
    }

    public function clear(): void
    {
        $this->resolvers = [];
    }

    /**
     * Alias for doctrine event listener.
     */
    public function onClear(): void
    {
        $this->clear();
    }
}
