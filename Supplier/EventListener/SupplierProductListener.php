<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class SupplierProductListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductListener
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

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
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event): void
    {
        $product = $this->getSupplierProductFromEvent($event);

        if (!$product->hasSubjectIdentity()) {
            return;
        }

        $subject = $this->subjectHelper->resolve($product);

        if (empty($product->getDesignation()) && !empty($subject->getDesignation())) {
            $product->setDesignation($subject->getDesignation());
        }
        if (!$product->getTaxGroup()) {
            $product->setTaxGroup($subject->getTaxGroup());
        }

        if (!$subject instanceof StockSubjectInterface) {
            return;
        }

        if (empty($product->getWeight())) {
            $product->setWeight($subject->getPackageWeight());
        }
        /* TODO if (empty($product->getUnit())) {
            $product->setUnit($subject->getUnit());
        }*/
    }

    /**
     * Returns the supplier product from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SupplierProductInterface
     */
    protected function getSupplierProductFromEvent(ResourceEventInterface $event): SupplierProductInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof SupplierProductInterface) {
            throw new InvalidArgumentException("Expected instance of " . SupplierProductInterface::class);
        }

        return $resource;
    }
}
