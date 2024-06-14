<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Helper;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentData;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;

use function array_push;
use function array_shift;
use function get_class;
use function min;
use function sprintf;

use const INF;

/**
 * Class AdjustHelper
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdjustHelper
{
    /** @var array<string, StockUnitRepositoryInterface> */
    private array $unitRepositories = [];

    private StockAdjustmentData $data;

    public function __construct(
        private readonly FactoryFactoryInterface    $factoryFactory,
        private readonly RepositoryFactoryInterface $repositoryFactory,
        private readonly ManagerFactoryInterface    $managerFactory,
    ) {
    }

    public function adjust(StockAdjustmentData $data): void
    {
        if ($data->quantity->isZero()) {
            return;
        }

        $subject = $data->subject;

        if ($subject->isStockCompound()) {
            throw new StockLogicException('Expected non-compound stock subject.');
        }

        if (StockSubjectModes::MODE_DISABLED === $subject->getStockMode()) {
            return;
        }

        $this->data = $data;

        if (StockAdjustmentReasons::isDebitReason($data->reason)) {
            $this->debitProduct();

            return;
        }

        $this->creditProduct();
    }

    private function debitProduct(): void
    {
        $subject = $this->data->subject;
        $quantity = clone $this->data->quantity;

        $units = $this->getStockUnits($subject, true);

        foreach ($units as $unit) {
            $qty = min($unit->getShippableQuantity(), $quantity);

            $adjustment = $this->createAdjustment();
            $adjustment
                ->setStockUnit($unit)
                ->setQuantity($qty);

            $this->getAdjustmentManager()->persist($adjustment);

            $quantity -= $qty;

            if ($quantity->isZero()) {
                return;
            }
        }

        if (!$quantity->isZero()) {
            throw new StockLogicException(sprintf(
                'Failed to adjust stock subject %d, %s remains.',
                $subject->getId(),
                $quantity->toFixed()
            ));
        }
    }

    private function creditProduct(): void
    {
        $subject = $this->data->subject;
        $quantity = clone $this->data->quantity;

        $units = $this->getStockUnits($subject, false);

        if (null === $unit = array_shift($units)) {
            $unit = $this->createUnit();
        }

        $adjustment = $this->createAdjustment();
        $adjustment
            ->setStockUnit($unit)
            ->setQuantity($quantity);

        $this->getAdjustmentManager()->persist($adjustment);
    }

    private function createUnit(): StockUnitInterface
    {
        $unit = $this->factoryFactory->getFactory($this->data->subject::getStockUnitClass())->create();

        if (!$unit instanceof StockUnitInterface) {
            throw new UnexpectedTypeException($unit, StockUnitInterface::class);
        }

        $unit->setSubject($this->data->subject);

        $this->getUnitManager()->persist($unit);

        return $unit;
    }

    private function createAdjustment(): StockAdjustmentInterface
    {
        /** @var StockAdjustmentInterface $adjustment */
        $adjustment = $this->factoryFactory->getFactory(StockAdjustmentInterface::class)->create();

        $adjustment
            ->setReason($this->data->reason)
            ->setNote($this->data->note);

        return $adjustment;
    }

    public function calculateMaxDebit(StockSubjectInterface $subject): Decimal
    {
        if ($subject->isStockCompound()) {
            throw new StockLogicException('Expected non-compound stock subject.');
        }

        if (StockSubjectModes::MODE_DISABLED === $subject->getStockMode()) {
            return new Decimal(INF);
        }

        $quantity = new Decimal(0);

        $units = $this->getStockUnits($subject, true);

        foreach ($units as $unit) {
            $quantity += $unit->getShippableQuantity();
        }

        return $quantity;
    }

    /**
     * @return iterable<int, StockUnitInterface>
     */
    private function getStockUnits(StockSubjectInterface $subject, bool $debitIntent): iterable
    {
        $repository = $this->getUnitRepository($subject);

        $units = $repository->findNotClosedBySubject($subject);

        if (!$debitIntent) {
            array_push($units, ...$repository->findLatestClosedBySubject($subject, 1));
        }

        // TODO Sort ?

        return $units;
    }

    private function getUnitRepository(StockSubjectInterface $subject): StockUnitRepositoryInterface
    {
        $class = get_class($subject);

        if (isset($this->unitRepositories[$class])) {
            return $this->unitRepositories[$class];
        }

        // TODO use \Ekyna\Component\Commerce\Stock\Helper\StockUnitHelper::getRepository
        $repository = $this->repositoryFactory->getRepository($subject::getStockUnitClass());

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new UnexpectedTypeException($repository, StockUnitRepositoryInterface::class);
        }

        return $this->unitRepositories[$class] = $repository;
    }

    private function getAdjustmentManager(): ResourceManagerInterface
    {
        return $this->managerFactory->getManager(StockAdjustmentInterface::class);
    }

    private function getUnitManager(): ResourceManagerInterface
    {
        return $this->managerFactory->getManager($this->data->subject::getStockUnitClass());
    }
}
