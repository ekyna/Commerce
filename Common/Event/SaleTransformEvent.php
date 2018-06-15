<?php

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;

/**
 * Class SaleTransformEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformEvent extends ResourceEvent
{
    /**
     * @var SaleInterface
     */
    private $source;


    /**
     * Constructor.
     *
     * @param SaleInterface $source
     * @param SaleInterface $target
     */
    public function __construct(SaleInterface $source, SaleInterface $target)
    {
        $this->setResource($target);

        $this->source = $source;
    }

    /**
     * Returns the source.
     *
     * @return SaleInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Returns the target.
     *
     * @return SaleInterface
     */
    public function getTarget()
    {
        return $this->getResource();
    }
}
