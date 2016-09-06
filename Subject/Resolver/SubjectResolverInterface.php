<?php

namespace Ekyna\Component\Commerce\Subject\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface SubjectResolverInterface
 * @package Ekyna\Component\Commerce\Subject\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectResolverInterface
{
    /**
     * Returns the subject from the sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return mixed
     */
    public function resolve(SaleItemInterface $item);

    /**
     * Generates the front office path for the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return string|null
     */
    public function generateFrontOfficePath(SaleItemInterface $item);

    /**
     * Generates the back office path for the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return string|null
     */
    public function generateBackOfficePath(SaleItemInterface $item);

    /**
     * Returns whether the resolver supports the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return boolean
     */
    public function supports(SaleItemInterface $item);

    /**
     * Returns the resolver name.
     *
     * @return string
     */
    public function getName();
}
