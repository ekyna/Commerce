<?php

namespace Ekyna\Component\Commerce\Common\Helper;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\CouponException;

/**
 * Class CouponHelper
 * @package Ekyna\Component\Commerce\Common\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponHelper
{
    use FormatterAwareTrait;

    /**
     * @var CouponRepositoryInterface
     */
    private $repository;

    /**
     * @var AmountCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param CouponRepositoryInterface $repository
     * @param AmountCalculatorInterface $calculator
     */
    public function __construct(CouponRepositoryInterface $repository, AmountCalculatorInterface $calculator)
    {
        $this->repository = $repository;
        $this->calculator = $calculator;
    }

    /**
     * Sets the sale's coupon.
     *
     * @param SaleInterface $sale
     * @param string        $code
     * @param bool          $check
     */
    public function set(SaleInterface $sale, string $code, bool $check = true): void
    {
        // Find coupon by its code
        if (!$coupon = $this->repository->findOneByCode($code)) {
            throw $this->createException("not_found");
        }

        if ($check) {
            // Check usage limit
            if ((0 < $limit = $coupon->getLimit()) && ($coupon->getUsage() >= $limit)) {
                throw $this->createException("usage");
            }

            // Check start date
            $today = new \DateTime();
            if (($date = $coupon->getStartAt()) && ($today < $date->setTime(0, 0, 0, 0))) {
                throw $this->createException("start_at", [
                    '%date%' => $this->getFormatter()->date($date),
                ]);
            }

            // Check end date
            if (($date = $coupon->getEndAt()) && ($today > $date->setTime(23, 59, 59, 999999))) {
                throw $this->createException("end_at", [
                    '%date%' => $this->getFormatter()->date($date),
                ]);
            }

            // Check minimum gross total
            if (0 < $min = $coupon->getMinGross()) {
                $currency = $this->calculator->getDefaultCurrency();
                $gross = $this->calculator->calculateSale($sale, $currency)->getGross();
                if (1 === Money::compare($min, $gross, $currency)) {
                    throw $this->createException("min_gross", [
                        '%gross%' => $this->getFormatter()->currency($min, $currency),
                    ]);
                }
            }

            // Check cumulative
            if (!$coupon->isCumulative() && $sale->hasDiscountItemAdjustment()) {
                throw $this->createException("cumulative");
            }
        }

        $sale
            ->setCoupon($coupon)
            ->setCouponData([
                // Adjustment
                'designation' => $this->getDesignation($coupon),
                'mode'        => $coupon->getMode(),
                'amount'      => $coupon->getAmount(),
                'source'      => 'coupon:' . $coupon->getId(),
                // Validity
                'gross'       => $coupon->getMinGross(),
                'cumulative'  => $coupon->isCumulative(),
            ]);
    }

    /**
     * @param SaleInterface $sale
     */
    public function clear(SaleInterface $sale): void
    {
        $sale
            ->setCoupon(null)
            ->setCouponData(null);
    }

    /**
     * Returns the coupon code designation.
     *
     * @param CouponInterface $coupon
     *
     * @return string
     */
    protected function getDesignation(CouponInterface $coupon): string
    {
        return $coupon->getDesignation() ?? "Coupon code {$coupon->getCode()}";
    }

    /**
     * Creates the exception.
     *
     * @param string $message
     * @param array  $parameters
     *
     * @return CouponException
     */
    protected function createException(string $message, array $parameters = []): CouponException
    {
        return new CouponException($message);
    }
}
