<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Logger;

use Decimal\Decimal;
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
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, '[Stock] ' . $message, $context);
    }

    public function unitSold(Model\StockUnitInterface $unit, Decimal $quantity, bool $relative = true): void
    {
        // unit.sold: {old} => {new} {unit: {id}, order: {id}, subject: {provider: {name}, id: {identifier}}

        $this->debug(sprintf('unit#%d sold: %s => %s',
            $unit->getId(),
            $unit->getSoldQuantity()->toFixed(5),
            ($relative ? $unit->getSoldQuantity() + $quantity : $quantity)->toFixed(5)
        ));
    }

    public function assignmentSold(Model\AssignmentInterface $assignment, Decimal $quantity, bool $relative = true): void
    {
        // assignment.sold: {old} => {new} {assignment: {id}, unit: {id}}

        $this->debug(sprintf('assignment#%d sold: %s => %s',
            $assignment->getId(),
            $assignment->getSoldQuantity()->toFixed(5),
            ($relative ? $assignment->getSoldQuantity() + $quantity : $quantity)->toFixed(5)
        ));
    }

    public function assignmentUnit(Model\AssignmentInterface $assignment, Model\StockUnitInterface $unit): void
    {
        // assignment.unit: {old} => {new} {assignment: {id}}

        $this->debug(sprintf('assignment#%d unit: %d => %d',
            $assignment->getId(),
            $assignment->getStockUnit()->getId(),
            $unit->getId()
        ));
    }
}
