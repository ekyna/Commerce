<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Event;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SaleDiscountListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleDiscountListener implements EventSubscriberInterface
{
    /**
     * @var CouponRepositoryInterface
     */
    private $couponRepository;


    /**
     * Constructor.
     *
     * @param CouponRepositoryInterface $couponRepository
     */
    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    /**
     * Sale discount event handler.
     *
     * @param Event\SaleEvent $event
     */
    public function onSaleDiscount(Event\SaleEvent $event)
    {
        if (null === $data = $event->getSale()->getCouponData()) {
            return;
        }

        $event->addAdjustmentData(new AdjustmentData(
            $data['mode'], $data['designation'], $data['amount'], $data['source']
        ));
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Event\SaleEvents::DISCOUNT => ['onSaleDiscount', 0],
        ];
    }
}
