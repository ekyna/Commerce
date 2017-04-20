<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ContextEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextEvent extends Event
{
    private ContextInterface $context;


    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the context.
     *
     * @return ContextInterface
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }
}
