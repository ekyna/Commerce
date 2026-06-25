<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Resolver;


use Ekyna\Component\Commerce\Common\Event\SaleItemNetPriceEvent;
use Ekyna\Component\Commerce\Common\Model;

/**
 * Class NetPriceResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NetPriceResolverInterface
{
    public function resolveSaleItem(Model\SaleItemInterface $item): SaleItemNetPriceEvent;
}
