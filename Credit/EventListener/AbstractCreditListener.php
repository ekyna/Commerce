<?php

namespace Ekyna\Component\Commerce\Credit\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Credit\Model\CreditInterface;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractCreditListener
 * @package Ekyna\Component\Commerce\Credit\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCreditListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var StockUnitAssignerInterface
     */
    protected $stockUnitAssigner;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the number generator.
     *
     * @param NumberGeneratorInterface $numberGenerator
     */
    public function setNumberGenerator(NumberGeneratorInterface $numberGenerator)
    {
        $this->numberGenerator = $numberGenerator;
    }

    /**
     * Sets the stock assigner.
     *
     * @param StockUnitAssignerInterface $stockUnitAssigner
     */
    public function setStockUnitAssigner(StockUnitAssignerInterface $stockUnitAssigner)
    {
        $this->stockUnitAssigner = $stockUnitAssigner;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $credit = $this->getCreditFromEvent($event);

        // Generate number and key
        $changed = $this->generateNumber($credit);

        /**
         * TODO Resource behaviors.
         */
        $credit
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($credit);
        }

        $sale = $credit->getSale();
        $sale->addCredit($credit); // TODO wtf ?

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $credit = $this->getCreditFromEvent($event);

        $this->preventSaleOrShipmentChange($credit);

        // Generate number and key
        $changed = $this->generateNumber($credit);

        /**
         * TODO Resource behaviors.
         */
        $credit->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($credit);

            $this->scheduleSaleContentChangeEvent($credit->getSale());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $credit = $this->getCreditFromEvent($event);

        $this->scheduleSaleContentChangeEvent($credit->getSale());
    }

    /**
     * Content change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $credit = $this->getCreditFromEvent($event);

        $this->scheduleSaleContentChangeEvent($credit->getSale());
    }

    /**
     * Generates the number.
     *
     * @param CreditInterface $credit
     *
     * @return bool Whether the credit has been generated or not.
     */
    protected function generateNumber(CreditInterface $credit)
    {
        if (0 == strlen($credit->getNumber())) {
            $this->numberGenerator->generate($credit);

            return true;
        }

        return false;
    }

    /**
     * Prevents the credit's sale or the credit's shipment from changing.
     *
     * @param CreditInterface $credit
     */
    abstract protected function preventSaleOrShipmentChange(CreditInterface $credit);

    /**
     * Dispatches the sale content change event.
     *
     * @param SaleInterface $sale
     */
    abstract protected function scheduleSaleContentChangeEvent(SaleInterface $sale);

    /**
     * Returns the credit from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return CreditInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getCreditFromEvent(ResourceEventInterface $event);
}
