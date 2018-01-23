<?php

namespace Ekyna\Component\Commerce\Stock\Logger;

use Ekyna\Component\Commerce\Stock\Model;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Class StockLogger
 * @package Ekyna\Component\Commerce\Stock\Logger
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockLogger extends AbstractLogger implements StockLoggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, '[Stock] ' . $message, $context);
    }

    /**
     * @inheritdoc                   $relative
     */
    public function unitSold(Model\StockUnitInterface $unit, $quantity, $relative = true)
    {
        // unit.sold: {old} => {new} {unit: {id}, order: {id}, subject: {provider: {name}, id: {identifier}}

        $this->debug(sprintf('unit#%d sold: %f => %f',
            $unit->getId(),
            $unit->getSoldQuantity(),
            $relative ? $unit->getSoldQuantity() + $quantity : $quantity
        ));
    }

    /**
     * @inheritdoc
     */
    public function assignmentSold(Model\StockAssignmentInterface $assignment, $quantity, $relative = true)
    {
        // assignment.sold: {old} => {new} {assignment: {id}, unit: {id}}

        $this->debug(sprintf('assignment#%d sold: %f => %f',
            $assignment->getId(),
            $assignment->getSoldQuantity(),
            $relative ? $assignment->getSoldQuantity() + $quantity : $quantity
        ));
    }

    /**
     * @inheritdoc
     */
    public function assignmentUnit(Model\StockAssignmentInterface $assignment, Model\StockUnitInterface $unit)
    {
        // assignment.unit: {old} => {new} {assignment: {id}}

        $this->debug(sprintf('assignment#%d unit: %d => %d',
            $assignment->getId(),
            $assignment->getStockUnit()->getId(),
            $unit->getId()
        ));
    }
}
