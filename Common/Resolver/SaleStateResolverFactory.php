<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

use function sprintf;

/**
 * Class SaleStateResolverFactory
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleStateResolverFactory
{
    protected StateResolverInterface $paymentStateResolver;
    protected StateResolverInterface $shipmentStateResolver;
    protected StateResolverInterface $invoiceStateResolver;
    protected array $map;
    protected array $resolvers = [];


    public function __construct(
        StateResolverInterface $paymentStateResolver,
        StateResolverInterface $shipmentStateResolver,
        StateResolverInterface $invoiceStateResolver,
        array $map
    ) {
        $this->paymentStateResolver = $paymentStateResolver;
        $this->shipmentStateResolver = $shipmentStateResolver;
        $this->invoiceStateResolver = $invoiceStateResolver;
        $this->map = $map;
    }

    public function getResolver(string $name): AbstractSaleStateResolver
    {
        if (isset($this->resolvers[$name])) {
            return $this->resolvers[$name];
        }

        if (!isset($this->map[$name])) {
            throw new InvalidArgumentException(sprintf("No resolver registered for '%s'.", $name));
        }

        /** @var AbstractSaleStateResolver $resolver */
        $resolver = new $this->map[$name]();

        $resolver->setPaymentStateResolver($this->paymentStateResolver);
        $resolver->setShipmentStateResolver($this->shipmentStateResolver);
        $resolver->setInvoiceStateResolver($this->invoiceStateResolver);

        return $this->resolvers[$name] = $resolver;
    }
}
