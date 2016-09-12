<?php

namespace Ekyna\Component\Commerce\Subject\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

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
     * Returns the resolver by sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return SubjectResolverInterface|null
     */
    public function getResolverByItem(SaleItemInterface $item);

    /**
     * Returns the resolvers.
     *
     * @return array|SubjectResolverInterface[]
     */
    public function getResolvers();
}
