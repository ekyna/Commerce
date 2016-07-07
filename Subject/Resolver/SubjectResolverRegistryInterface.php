<?php

namespace Ekyna\Component\Commerce\Subject\Resolver;

/**
 * Interface SubjectResolverRegistryInterface
 * @package Ekyna\Component\Commerce\Subject\Resolver
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectResolverRegistryInterface
{
    /**
     * Adds the subject resolver.
     *
     * @param SubjectResolverInterface $resolver
     */
    public function addResolver(SubjectResolverInterface $resolver);

    /**
     * Returns the resolvers.
     *
     * @return array|SubjectResolverInterface[]
     */
    public function getResolvers();
}
