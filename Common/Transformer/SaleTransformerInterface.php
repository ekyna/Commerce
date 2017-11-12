<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Class SaleTransformerInterface
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleTransformerInterface
{
    /**
     * Initializes the sale transformation.
     *
     * @param SaleInterface $source The source sale.
     * @param SaleInterface $target The target sale.
     *
     * @return \Ekyna\Component\Resource\Event\ResourceEventInterface The target initialize event.
     */
    public function initialize(SaleInterface $source, SaleInterface $target);

    /**
     * Transforms the given source sale to the given target sale.
     *
     * @return \Ekyna\Component\Resource\Event\ResourceEventInterface|null The event that stopped transformation if any.
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException If initialize has not been called first.
     */
    public function transform();
}
