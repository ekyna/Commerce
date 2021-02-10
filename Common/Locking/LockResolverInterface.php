<?php

namespace Ekyna\Component\Commerce\Common\Locking;

use DateTime;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface LockResolverInterface
 * @package Ekyna\Component\Commerce\Common\Locking
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface LockResolverInterface
{
    /**
     * Returns whether the given resource is supported.
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function support(ResourceInterface $resource): bool;

    /**
     * Resolves the date to check.
     *
     * @param ResourceInterface $resource
     *
     * @return DateTime|null
     */
    public function resolve(ResourceInterface $resource): ?DateTime;
}
