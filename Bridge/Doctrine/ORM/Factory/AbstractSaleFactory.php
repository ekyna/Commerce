<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory;

use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

use function is_null;

/**
 * Class AbstractSaleFactory
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleFactory extends ResourceFactory implements SaleFactoryInterface
{
    private FactoryHelperInterface    $factoryHelper;
    private SaleUpdaterInterface      $saleUpdater;
    private LocaleProviderInterface   $localeProvider;
    private CurrencyProviderInterface $currencyProvider;

    public function __construct(
        FactoryHelperInterface    $factoryHelper,
        SaleUpdaterInterface      $saleUpdater,
        LocaleProviderInterface   $localeProvider,
        CurrencyProviderInterface $currencyProvider
    ) {
        $this->factoryHelper = $factoryHelper;
        $this->saleUpdater = $saleUpdater;
        $this->localeProvider = $localeProvider;
        $this->currencyProvider = $currencyProvider;
    }

    public function create(bool $initialize = true): ResourceInterface
    {
        $sale = parent::create();

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        $initialize && $this->initialize($sale);

        return $sale;
    }

    /* Used by fixtures */
    public function createWithCustomer(CustomerInterface $customer): SaleInterface
    {
        $sale = parent::create();

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        $sale->setCustomer($customer);

        $this->initialize($sale);

        return $sale;
    }

    public function initialize(SaleInterface $sale): void
    {
        $sale->setContext(null);

        if ($customer = $sale->getCustomer()) {
            $this->initFromCustomer($sale, $customer);
        }

        if (!$sale->getLocale()) {
            $sale->setLocale($this->localeProvider->getCurrentLocale());
        }

        if (!$sale->getCurrency()) {
            $sale->setCurrency($this->currencyProvider->getCurrency());
        }

        $this->saleUpdater->updateShipmentMethodAndAmount($sale);
    }

    private function initFromCustomer(SaleInterface $sale, CustomerInterface $customer): void
    {
        if (!$sale->getLocale()) {
            $sale->setLocale($customer->getLocale());
        }

        if (!$sale->getCurrency()) {
            $sale->setCurrency($customer->getCurrency());
        }

        $invoiceDefault = $customer->getDefaultInvoiceAddress(true);
        if (is_null($sale->getInvoiceAddress()) && $invoiceDefault) {
            $sale->setInvoiceAddress(
                $this->factoryHelper->createAddressForSale($sale, $invoiceDefault)
            );
        }

        $sale->setSameAddress(true);

        $deliveryDefault = $customer->getDefaultDeliveryAddress(true);
        if (is_null($sale->getDeliveryAddress()) && $deliveryDefault && ($deliveryDefault !== $invoiceDefault)) {
            $sale
                ->setSameAddress(false)
                ->setDeliveryAddress(
                    $this->factoryHelper->createAddressForSale($sale, $invoiceDefault)
                );
        }
    }
}
