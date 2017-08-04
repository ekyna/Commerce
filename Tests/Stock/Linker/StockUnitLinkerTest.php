<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Linker;

use Acme\Product\Entity\Product;
use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Stock\Linker\StockUnitLinker;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Supplier\Entity\Supplier;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderItem;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierProduct;
use Ekyna\Component\Commerce\Tests\BaseTestCase;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitLinkerTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitLinkerTest extends BaseTestCase
{
    public function testLinkItemWithoutAssignment()
    {
        $linker = $this->createStockUnitLinker();

        $supplier = new Supplier();
        $supplier
            ->setCurrency($this->getDefaultCurrency())
            ->setName('Supplier A');

        $supplierOrder = new SupplierOrder();
        $supplierOrder
            ->setCurrency($this->getDefaultCurrency())
            ->setSupplier($supplier);

        $subject = new Product();
        $subject
            ->setDesignation('Product A')
            ->setReference('ACME-A')
            ->setNetPrice(18)
            ->setWeight(0.5)
            ->setStockMode(StockSubjectModes::MODE_ENABLED);

        $product = new SupplierProduct();
        $product
            ->setSupplier($supplier)
            ->setDesignation('Product A')
            ->setReference('PROD-A')
            ->setNetPrice(12)
            ->setWeight(0.5)
            ->getSubjectIdentity()
                ->setProvider('acme')
                ->setIdentifier(1);

        $supplierOrderItem = new SupplierOrderItem();
        $supplierOrderItem
            ->setDesignation('Product A')
            ->setReference('PROD-A')
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder)
            ->setProduct();

        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn(null);

        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn((new StockUnit())->setSubject($subject));

        $linker->linkItem($supplierOrderItem);

        $stockUnit = $supplierOrderItem->getStockUnit();
        $this->assertNotNull($stockUnit);

        $this->assertEquals(20, $stockUnit->getOrderedQuantity());
        $this->assertEquals(0, $stockUnit->getSoldQuantity());
        $this->assertEquals(12, $stockUnit->getNetPrice());
        $this->assertEquals(StockUnitStates::STATE_NEW, $stockUnit->getState());
        $this->assertEquals($subject, $stockUnit->getSubject());
        $this->assertEmpty($stockUnit->getStockAssignments());
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StockUnitResolverInterface
     */
    private $unitResolver;

    /**
     * Returns the persistence helper.
     *
     * @return PersistenceHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPersistenceHelper()
    {
        if (null !== $this->persistenceHelper) {
            return $this->persistenceHelper;
        }

        return $this->persistenceHelper = $this->createMock(PersistenceHelperInterface::class);
    }

    /**
     * Returns the unit resolver.
     *
     * @return StockUnitResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUnitResolver()
    {
        if (null !== $this->unitResolver) {
            return $this->unitResolver;
        }

        return $this->unitResolver = $this->createMock(StockUnitResolverInterface::class);
    }

    /**
     * Creates a stock unit linker.
     *
     * @return StockUnitLinker
     */
    private function createStockUnitLinker()
    {
        $unitStateResolver = new StockUnitStateResolver();

        return new StockUnitLinker(
            $this->getPersistenceHelper(),
            $this->getUnitResolver(),
            $unitStateResolver,
            $this->createSaleFactory()
        );
    }
}
