<?php

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ContextEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextEvent extends Event
{
    /**
     * @var ContextInterface
     */
    private $context;


    /**
     * Constructor.
     *
     * @param ContextInterface $context
     */
    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the context.
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
