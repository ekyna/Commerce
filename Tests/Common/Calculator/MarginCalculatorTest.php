<?php

namespace Ekyna\Component\Commerce\Tests\Common\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculator;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculator;
use Ekyna\Component\Commerce\Common\Model\Amount;
use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use Ekyna\Component\Resource\Model\ResourceInterface;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class MarginCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MarginCalculatorTest extends TestCase
{
    /**
     * @var AmountCalculatorFactory|MockObject
     */
    private $amountCalculatorFactory;

    /**
     * @var InvoiceSubjectCalculatorInterface|MockObject
     */
    private $invoiceCalculator;


    protected function setUp(): void
    {
        $this->amountCalculatorFactory = $this->createMock(AmountCalculatorFactory::class);
        $this->amountCalculatorFactory
            ->method('create')
            ->willReturnCallback(function (string $currency, bool $revenue) {
                $calculator = new AmountCalculator($currency, $revenue);

                $calculator->setCurrencyConverter($this->getCurrencyConverter());
                $calculator->setInvoiceCalculator($this->invoiceCalculator);
                $calculator->setAmountCalculatorFactory($this->amountCalculatorFactory);

                return $calculator;
            });

        $this->invoiceCalculator = $this->createMock(InvoiceSubjectCalculatorInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->amountCalculatorFactory = null;
        $this->invoiceCalculator = null;
    }

    /**
     * Returns a new instance of margin calculator.
     *
     * @param string          $currency
     * @param bool            $profit
     * @param StatFilter|null $filter
     *
     * @return MarginCalculator
     */
    private function createCalculator(
        string $currency,
        bool $profit = false,
        StatFilter $filter = null
    ): MarginCalculator {
        $calculator = new MarginCalculator($currency, $profit, $filter);

        $calculator->setCalculatorFactory($this->amountCalculatorFactory);
        $calculator->setInvoiceCalculator($this->invoiceCalculator);
        $calculator->setWeightCalculator($this->getShipmentWeightCalculatorMock());
        $calculator->setShipmentPriceResolver($this->getShipmentPriceResolverMock());
        $calculator->setShipmentAddressResolver($this->getShipmentAddressResolverMock());
        $calculator->setSubjectHelper($this->getSubjectHelperMock());
        $calculator->setCurrencyConverter($this->getCurrencyConverter());
        $calculator->setPurchaseCostGuesser($this->getPurchaseCostGuesserMock());

        return $calculator;
    }

    /**
     * @param array      $sale      The sale model
     * @param array      $expected  The expected result
     * @param array      $amounts   The amount calculator config
     * @param array      $subjects  The subject helper config
     * @param array|null $costs     The purchase cost guesser config
     * @param array|null $addresses The shipment address resolver config
     * @param array|null $weights   The shipment weight calculator config
     * @param array|null $prices    The shipment price resolver config
     *
     * @dataProvider provide_calculateSale
     */
    public function test_calculateSale(
        array $sale,
        array $expected,
        array $amounts = [],
        array $subjects = [],
        array $costs = null,
        array $addresses = null,
        array $weights = null,
        array $prices = null
    ): void {
        $currency = Fixture::CURRENCY_EUR;

        Fixture::order($sale);

        $this->configureAmounts($currency, $amounts);
        $this->configureSubjectHelper($subjects);
        $this->configurePurchaseCostGuesser($costs);
        $this->configureShipmentAddressResolver($addresses);
        $this->configureShipmentWeightCalculator($weights);
        $this->configureShipmentPriceResolver($prices);
        $this->configureInvoiceCalculator(); // Fully invoiced for now

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR, false);
        $this->assertMargins($calculator, $expected['default']);

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR, true);
        $this->assertMargins($calculator, $expected['profit']);
    }

    public function provide_calculateSale(): \Generator
    {
        yield 'Case 1' => [
            'sale'     => Data::order3(),
            'expected' => [
                'default' => [
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item1',
                        'margin' => [92.82, 29.67, false],
                    ],
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item2',
                        'margin' => [107.40, 17.56, true],
                    ],
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item3_1',
                        'margin' => [254.88, 32.48, false],
                    ],
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item3_2',
                        'margin' => [245.20, 42.22, false],
                    ],
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item3',
                        'margin' => [500.08, 36.62, false],
                    ],
                    [
                        'type'   => 'sale',
                        'ref'    => 'order3',
                        'margin' => [700.30, 30.58, true],
                    ],
                    [
                        'type'   => 'shipment',
                        'ref'    => 'order3',
                        'margin' => null,
                    ],
                ],
                'profit'  => [
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item1',
                        'margin' => [26.58, 8.50, false],
                    ],
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item2',
                        'margin' => [45.96, 7.51, true],
                    ],
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item3_1',
                        'margin' => [137.05, 17.47, false],
                    ],
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item3_2',
                        'margin' => [170.44, 29.35, false],
                    ],
                    [
                        'type'   => 'item',
                        'ref'    => 'order3_item3',
                        'margin' => [307.49, 22.52, false],
                    ],
                    [
                        'type'   => 'sale',
                        'ref'    => 'order3',
                        'margin' => [345.37, 15.00, true],
                    ],
                    [
                        'type'   => 'shipment',
                        'ref'    => 'order3',
                        'margin' => [-34.66, -280.88, false],
                    ],
                ],
            ],
            'amounts'  => [
                'calculateSaleItem' => [
                    'order3_item1'     => [78.20, 312.80, 0, 312.80, 31.28, 344.08],
                    'order3_item2'     => [69.50, 695.00, 83.40, 611.60, 61.16, 672.76],
                    'order3_item3_1'   => [82.60, 826.00, 41.30, 784.70, 156.94, 941.64],
                    'order3_item3_2'   => [165.00, 660.00, 79.20, 580.80, 116.16, 696.96],
                    'order3_item3_2_1' => [24.90, 398.4, 0., 398.4, 0, 398.4],
                    'order3_item3'     => [0.00, 0.00, 0.00, 0.00, 0.00, 0.00],
                ],
            ],
            'subjects' => [
                'order3_item2' => 'subject2',
            ],
            'costs'    => [
                'subject2' => [52.31, 56.69],
            ],
            'address'  => [
                'order3_shipment1' => 'order3_invoiceAddress',
                'order3_shipment2' => 'order3_invoiceAddress',
            ],
            'weight'   => [
                'order3_shipment1' => 45.,
                'order3_shipment2' => 15.,
            ],
            'price'    => [
                [
                    'method'  => Fixture::SHIPMENT_METHOD_UPS,
                    'country' => Fixture::COUNTRY_FR,
                    'weight'  => 45.,
                    'price'   => 35.,
                ],
                [
                    'method'  => Fixture::SHIPMENT_METHOD_DHL,
                    'country' => Fixture::COUNTRY_FR,
                    'weight'  => 15.,
                    'price'   => 12.,
                ],
            ],
        ];

        // TODO Test not compound with public children item.
    }

    private function assertMargins(MarginCalculator $calculator, array $expected): void
    {
        foreach ($expected as $config) {
            $reference = $config['ref'];
            if ($config['type'] === 'item') {
                $margin = $calculator->calculateSaleItem(Fixture::orderItem($reference));
            } elseif ($config['type'] === 'sale') {
                $margin = $calculator->calculateSale(Fixture::order($reference));
            } elseif ($config['type'] === 'shipment') {
                $margin = $calculator->calculateSaleShipment(Fixture::order($reference));
            } else {
                throw new LogicException("Unexpected type.");
            }

            if (null === $config['margin']) {
                $this->assertNull($margin);

                continue;
            }

            $this->assertSame($config['margin'][0], $margin->getAmount(), "Wrong margin amount for '$reference'.");
            $this->assertSame($config['margin'][1], $margin->getPercent(), "Wrong margin percentage for '$reference'.");
            $this->assertSame($config['margin'][2], $margin->isAverage(), "Wrong margin average '$reference'.");
        }
    }

    private function configureAmounts(string $currency, array $config = null): void
    {
        if (empty($config)) {
            return;
        }

        foreach ($config as $method => $amounts) {
            $map = [];
            foreach ($amounts as $reference => $amount) {
                /** @var ResourceInterface $object */
                $object = Fixture::get($reference);
                $map[$object->getId()] = $amount ? new Amount($currency, ...$amount) : null;
            }

            $this
                ->getAmountCalculatorMock()
                ->method($method)
                ->willReturnCallback(function (ResourceInterface $object) use ($map) {
                    return $map[$object->getId()];
                });
        }
    }

    private function configureSubjectHelper(array $subjects): void
    {
        if (empty($subjects)) {
            return;
        }

        $map = [];
        foreach ($subjects as $item => $subject) {
            $item = Fixture::orderItem($item);
            $subject = Fixture::subject($subject);

            $map[$item->getId()] = $subject;
        }

        $this
            ->getSubjectHelperMock()
            ->method('resolve')
            ->willReturnCallback(function (OrderItemInterface $item) use ($map) {
                return $map[$item->getId()];
            });
    }

    private function configurePurchaseCostGuesser(array $costs): void
    {
        if (empty($costs)) {
            return;
        }

        $map = [];
        foreach ($costs as $subject => $cost) {
            $subject = Fixture::subject($subject);

            $map[$subject->getId()] = $cost;
        }

        $this
            ->getPurchaseCostGuesserMock()
            ->method('guess')
            ->willReturnCallback(function (SubjectInterface $object, $currency, $shipping) use ($map) {
                return $map[$object->getId()][$shipping ? 1 : 0];
            });
    }

    private function configureShipmentAddressResolver(array $addresses): void
    {
        if (empty($addresses)) {
            return;
        }

        $map = [];
        foreach ($addresses as $shipment => $address) {
            $shipment = Fixture::shipment($shipment);
            $map[$shipment->getId()] = Fixture::get($address);
        }

        $this
            ->getShipmentAddressResolverMock()
            ->method('resolveReceiverAddress')
            ->willReturnCallback(function (ShipmentInterface $shipment) use ($map) {
                return $map[$shipment->getId()];
            });
    }

    private function configureShipmentWeightCalculator(array $weights): void
    {
        if (empty($weights)) {
            return;
        }

        $map = [];
        foreach ($weights as $shipment => $weight) {
            $shipment = Fixture::shipment($shipment);

            $map[$shipment->getId()] = $weight;
        }

        $this
            ->getShipmentWeightCalculatorMock()
            ->method('calculateShipment')
            ->willReturnCallback(function (ShipmentInterface $shipment) use ($map) {
                return $map[$shipment->getId()];
            });
    }

    private function configureShipmentPriceResolver(array $prices): void
    {
        if (empty($prices)) {
            return;
        }

        $map = [];
        foreach ($prices as $shipment => $config) {
            $config['method'] = Fixture::shipmentMethod($config['method']);
            $config['country'] = Fixture::country($config['country']);

            $map[] = $config;
        }

        $this
            ->getShipmentPriceResolverMock()
            ->method('getPriceByCountryAndMethodAndWeight')
            ->willReturnCallback(function ($country, $method, $weight) use ($map) {
                foreach ($map as $config) {
                    if ($config['method'] !== $method) {
                        continue;
                    }
                    if ($config['country'] !== $country) {
                        continue;
                    }
                    if ($config['weight'] !== $weight) {
                        continue;
                    }

                    return new ResolvedShipmentPrice($method, $config['weight'], $config['price']);
                }

                return null;
            });
    }

    private function configureInvoiceCalculator()
    {
        $this
            ->invoiceCalculator
            ->method('calculateInvoicedQuantity')
            ->willReturnCallback(function ($subject) {
                if ($subject instanceof SaleItemInterface) {
                    return $subject->getTotalQuantity();
                }
                if ($subject instanceof SaleAdjustmentInterface) {
                    return 1;
                }
                if ($subject instanceof SaleInterface) {
                    return 1;
                }
                throw new LogicException("Unexpect argument");
            });

        $this
            ->invoiceCalculator
            ->method('calculateCreditedQuantity')
            ->willReturnCallback(function () {
                return 0;
            });
    }

    // TODO test filter

    // TODO test cached margins
}
