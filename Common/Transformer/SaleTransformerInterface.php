<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

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
     * @return ResourceEventInterface The target initialize event.
     */
    public function initialize(SaleInterface $source, SaleInterface $target): ResourceEventInterface;

    /**
     * Transforms the given source sale to the given target sale.
     *
     * @return ResourceEventInterface|null The event that stopped transformation if any.
     *
     * @throws LogicException If initialize has not been called first.
     */
    public function transform(): ?ResourceEventInterface;
}
