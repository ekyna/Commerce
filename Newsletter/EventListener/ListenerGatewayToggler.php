<?php

namespace Ekyna\Component\Commerce\Newsletter\EventListener;

/**
 * Class ListenerGatewayToggler
 * @package Ekyna\Component\Commerce\Newsletter\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ListenerGatewayToggler
{
    /**
     * @var ListenerInterface[]
     */
    private $listeners = [];


    /**
     * Constructor.
     *
     * @param array $listeners
     */
    public function __construct(array $listeners)
    {
        foreach ($listeners as $listener) {
            $this->addListener($listener);
        }
    }

    /**
     * Adds the listener.
     *
     * @param ListenerInterface $listener
     */
    public function addListener(ListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * Enables the listeners.
     */
    public function enable(): void
    {
        foreach ($this->listeners as $listener) {
            $listener->setEnabled(true);
        }
    }

    /**
     * Disables the listeners.
     */
    public function disable(): void
    {
        foreach ($this->listeners as $listener) {
            $listener->setEnabled(false);
        }
    }
}
