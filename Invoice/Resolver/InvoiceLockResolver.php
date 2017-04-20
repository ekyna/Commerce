<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use DateTime;
use Ekyna\Component\Commerce\Common\Locking\LockResolverInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class InvoiceLockResolver
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLockResolver implements LockResolverInterface
{
    public function support(ResourceInterface $resource): bool
    {
        return $resource instanceof InvoiceInterface;
    }

    /**
     * @param InvoiceInterface $resource
     */
    public function resolve(ResourceInterface $resource): ?DateTime
    {
        return $resource->getCreatedAt();
    }
}
