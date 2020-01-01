<?php

namespace Ekyna\Component\Commerce\Customer\EventListener;

use Ekyna\Component\Commerce\Customer\Loyalty\LoyaltyUpdater;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class LoyaltyListener
 * @package Ekyna\Component\Commerce\Customer\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyListener
{
    /**
     * @var Features
     */
    private $features;

    /**
     * @var LoyaltyUpdater
     */
    private $updater;


    /**
     * Constructor.
     *
     * @param Features       $features
     * @param LoyaltyUpdater $updater
     */
    public function __construct(Features $features, LoyaltyUpdater $updater)
    {
        $this->features = $features;
        $this->updater = $updater;
    }

    /**
     * Customer birthday event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onBirthday(ResourceEventInterface $event): void
    {
        $customer = $this->getCustomerFromEvent($event);

        $points = (int)$this->features->getConfig(Features::LOYALTY)['credit'][Features::BIRTHDAY];

        if (0 >= $points) {
            return;
        }

        $this->updater->add($customer, $points, 'Birthday ' . date('Y'));
    }

    /**
     * Newsletter subscribe event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onNewsletterSubscribe(ResourceEventInterface $event): void
    {
        $customer = $this->getCustomerFromEvent($event);

        $points = (int)$this->features->getConfig(Features::LOYALTY)['credit'][Features::NEWSLETTER];

        if (0 >= $points) {
            return;
        }

        $this->updater->add($customer, $points, 'Newsletter subscribed');
    }

    /**
     * Order completed event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onOrderCompleted(ResourceEventInterface $event): void
    {
        $order = $this->getOrderFromEvent($event);

        if ($order->isSample()) {
            return;
        }

        if (null === $customer = $order->getCustomer()) {
            return;
        }

        $points = floor($order->getGrandTotal() * (float)$this->features->getConfig(Features::LOYALTY)['credit_rate']);

        if (0 >= $points) {
            return;
        }

        $this->updater->add($customer, $points, 'Order ' . $order->getNumber());
    }

    /**
     * Returns the customer from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return CustomerInterface
     */
    private function getCustomerFromEvent(ResourceEventInterface $event): CustomerInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof CustomerInterface) {
            throw new InvalidArgumentException('Expected instance of ' . CustomerInterface::class);
        }

        return $resource;
    }

    /**
     * Returns the order from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return OrderInterface
     */
    private function getOrderFromEvent(ResourceEventInterface $event): OrderInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInterface) {
            throw new InvalidArgumentException('Expected instance of ' . OrderInterface::class);
        }

        return $resource;
    }
}
