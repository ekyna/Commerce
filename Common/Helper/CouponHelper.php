<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Helper;

use DateTime;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Exception\CouponException;

/**
 * Class CouponHelper
 * @package Ekyna\Component\Commerce\Common\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponHelper
{
    use FormatterAwareTrait;

    private CouponRepositoryInterface $couponRepository;
    private AmountCalculatorFactory   $calculatorFactory;
    private string                    $defaultCurrency;


    public function __construct(
        CouponRepositoryInterface $repository,
        AmountCalculatorFactory $factory,
        string $currency
    ) {
        $this->couponRepository = $repository;
        $this->calculatorFactory = $factory;
        $this->defaultCurrency = $currency;
    }

    /**
     * Sets the sale's coupon.
     */
    public function set(SaleInterface $sale, string $code, bool $check = true): void
    {
        // Find coupon by its code
        if (!$coupon = $this->couponRepository->findOneByCode($code)) {
            throw $this->createException('not_found');
        }

        if ($check) {
            // Check owner
            if ($coupon->getCustomer() && $sale->getCustomer() !== $coupon->getCustomer()) {
                throw $this->createException('owner');
            }

            // Check usage limit
            if ((0 < $limit = $coupon->getLimit()) && ($coupon->getUsage() >= $limit)) {
                throw $this->createException('usage');
            }

            // Check start date
            $today = new DateTime();
            if (($date = $coupon->getStartAt()) && ($today < $date->setTime(0, 0))) {
                throw $this->createException('start_at', [
                    '%date%' => $this->getFormatter()->date($date),
                ]);
            }

            // Check end date
            if (($date = $coupon->getEndAt()) && ($today > $date->setTime(23, 59, 59, 999999))) {
                throw $this->createException('end_at', [
                    '%date%' => $this->getFormatter()->date($date),
                ]);
            }

            // Check minimum gross total
            if (0 < $min = $coupon->getMinGross()) {
                $gross = $this
                    ->calculatorFactory
                    ->create($this->defaultCurrency)
                    ->calculateSale($sale)
                    ->getGross();

                if ($min > $gross) {
                    throw $this->createException('min_gross', [
                        '%gross%' => $this->getFormatter()->currency($min, $this->defaultCurrency),
                    ]);
                }
            }

            // Check cumulative
            if (!$coupon->isCumulative() && $sale->hasDiscountItemAdjustment()) {
                throw $this->createException('cumulative');
            }
        }

        $sale
            ->setCoupon($coupon)
            ->setCouponData([
                // Adjustment
                'designation' => $this->getDesignation($coupon),
                'mode'        => $coupon->getMode(),
                'amount'      => $coupon->getAmount()->toFixed(5),
                'source'      => 'coupon:' . $coupon->getId(),
                // Validity
                'gross'       => $coupon->getMinGross()->toFixed(5),
                'cumulative'  => $coupon->isCumulative(),
            ]);
    }

    public function clear(SaleInterface $sale): void
    {
        $sale
            ->setCoupon(null)
            ->setCouponData(null);
    }

    /**
     * Returns the coupon code designation.
     */
    protected function getDesignation(CouponInterface $coupon): string
    {
        return $coupon->getDesignation() ?? "Coupon code {$coupon->getCode()}";
    }

    /**
     * Creates the exception.
     */
    protected function createException(string $message, array $parameters = []): CouponException
    {
        return new CouponException($message);
    }
}
