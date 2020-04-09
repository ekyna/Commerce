<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnitOverflowChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UnitOverflowChecker extends AbstractChecker
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OverflowHandlerInterface
     */
    private $overflowHandler;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param OverflowHandlerInterface $overflowHandler
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        OverflowHandlerInterface $overflowHandler
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->overflowHandler = $overflowHandler;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return "Stock unit overflow";
    }

    /**
     * @inheritDoc
     */
    public function build(OutputInterface $output): void
    {
        $repository = $this->entityManager->getRepository(AbstractStockUnit::class);

        foreach ($this->results as $result) {
            /** @var \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface $unit */
            if (!$unit = $repository->find($result['id'])) {
                throw new \Exception("Unit not found.");
            }

            $this->overflowHandler->handle($unit);
        }

        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    protected function getMap(): array
    {
        return [
            'id'         => 'ID',
            'product_id' => 'Product',
            'ordered'    => 'Ordered',
            'received'   => 'Received',
            'adjusted'   => 'Adjusted',
            'sold'       => 'Sold',
            'shipped'    => 'Shipped',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSql(): string
    {
        return <<<SQL
SELECT u.id, 
    u.product_id, 
    u.ordered_quantity AS ordered, 
    u.received_quantity AS received,
    u.adjusted_quantity AS adjusted,
    u.sold_quantity AS sold, 
    u.shipped_quantity AS shipped
FROM commerce_stock_unit AS u
WHERE u.ordered_quantity<u.received_quantity 
   OR (u.supplier_order_item_id IS NOT NULL AND (u.adjusted_quantity+u.ordered_quantity)<u.sold_quantity)
   OR (u.supplier_order_item_id IS NULL AND state!='new' AND 0<u.adjusted_quantity AND u.adjusted_quantity<u.sold_quantity)
   OR (u.adjusted_quantity+u.received_quantity)<u.shipped_quantity
   OR u.sold_quantity<u.shipped_quantity
SQL;
    }
}
