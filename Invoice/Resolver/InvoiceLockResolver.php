<?php

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
    /**
     * @inheritDoc
     */
    public function support(ResourceInterface $resource): bool
    {
        return $resource instanceof InvoiceInterface;
    }

    /**
     * @inheritDoc
     *
     * @param InvoiceInterface $resource
     */
    public function resolve(ResourceInterface $resource): ?DateTime
    {
        return $resource->getCreatedAt();
    }
}
