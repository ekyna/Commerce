<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Event;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SaleDiscountListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleDiscountListener implements EventSubscriberInterface
{
    public function onSaleDiscount(Event\SaleEvent $event): void
    {
        if (null === $data = $event->getSale()->getCouponData()) {
            return;
        }

        $event->addAdjustmentData(new AdjustmentData(
            $data['mode'], $data['designation'], $data['amount'], $data['source']
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Event\SaleEvents::DISCOUNT => ['onSaleDiscount', 0],
        ];
    }
}
