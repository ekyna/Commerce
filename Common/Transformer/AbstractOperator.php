<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractOperator
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AbstractOperator
{
    protected ?SaleInterface $source = null;
    protected ?SaleInterface $target = null;

    public function __construct(
        protected readonly SaleCopierFactoryInterface $saleCopierFactory,
        protected readonly FactoryFactoryInterface    $factoryFactory,
        protected readonly ManagerFactoryInterface    $managerFactory,
        protected readonly EventDispatcherInterface   $eventDispatcher
    ) {
    }

    /**
     * Returns the factory for the given sale.
     */
    protected function getFactory(SaleInterface $sale): SaleFactoryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->factoryFactory->getFactory(
            $this->resolveInterface($sale)
        );
    }

    /**
     * Returns the manager for the given sale.
     */
    protected function getManager(SaleInterface $sale): ResourceManagerInterface
    {
        return $this->managerFactory->getManager(
            $this->resolveInterface($sale)
        );
    }

    private function resolveInterface(SaleInterface $sale): string
    {
        $interfaces = [CartInterface::class, QuoteInterface::class, OrderInterface::class];

        foreach ($interfaces as $interface) {
            if (!$sale instanceof $interface) {
                continue;
            }

            return $interface;
        }

        throw new UnexpectedTypeException($sale, $interfaces);
    }
}
