<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;

/**
 * Interface StateResolverInterface
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StateResolverInterface
{
    /**
     * Resolves the subject state.
     *
     * @return bool Whether the state(s) has been changed.
     *
     * @throws CommerceExceptionInterface
     */
    public function resolve(object $subject): bool;
}
