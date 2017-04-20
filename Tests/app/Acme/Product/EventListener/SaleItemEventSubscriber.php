<?php

namespace Acme\Product\EventListener;

use Acme\Product\Entity\Product;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class SaleItemEventSubscriber
 * @package Acme\Product\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEventSubscriber
{
    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     */
    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Sale item build event handler.
     *
     * @param SaleItemEvent $event
     */
    public function onSaleItemBuild(SaleItemEvent $event)
    {
        if (null === $product = $this->getProductFromEvent($event)) {
            return;
        }

        $item = $event->getItem();

        $this->subjectHelper->assign($item, $product);

        $item
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setNetPrice(clone $product->getNetPrice())
            ->setWeight(clone $product->getPackageWeight())
            ->setTaxGroup($product->getTaxGroup());
    }

    /**
     * Returns the product from the given event.
     *
     * @param SaleItemEvent $event
     *
     * @return null|Product
     */
    protected function getProductFromEvent(SaleItemEvent $event)
    {
        $item = $event->getItem();

        $product = $this->subjectHelper->resolve($item, false);
        if ($product instanceof Product) {
            return $product;
        }

        return null;
    }
}
