<?php

namespace Ekyna\Component\Commerce\Common\Resolver;

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
     * @param object $subject
     *
     * @return bool Whether or not the state(s) has been changed.
     *
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function resolve(object $subject): bool;
}
