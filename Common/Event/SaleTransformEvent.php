<?php

declare(strict_types=1);

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
    private SaleInterface $source;


    public function __construct(SaleInterface $source, SaleInterface $target)
    {
        $this->setResource($target);

        $this->source = $source;
    }

    public function getSource(): SaleInterface
    {
        return $this->source;
    }

    public function getTarget(): SaleInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getResource();
    }
}
