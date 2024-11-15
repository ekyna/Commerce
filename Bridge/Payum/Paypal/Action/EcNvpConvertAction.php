<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;

use function implode;

/**
 * Class EcNvpConvertAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EcNvpConvertAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    private AmountCalculatorFactory $calculatorFactory;
    private ?string                 $brandName;

    private ?string                    $currency   = null;
    private int                        $line;
    private ?AmountCalculatorInterface $calculator = null;

    public function __construct(AmountCalculatorFactory $calculatorFactory, string $brandName = null)
    {
        $this->calculatorFactory = $calculatorFactory;
        $this->brandName = $brandName;
    }

    /**
     * @inheritDoc
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $this->currency = $payment->getCurrency()->getCode();

        $details = (array)ArrayObject::ensureArrayObject($payment->getDetails());

        $details['NOSHIPPING'] = 1;

        if (!empty($this->brandName)) {
            $details['BRANDNAME'] = $this->brandName;
        }

        $details['PAYMENTREQUEST_0_INVNUM'] = $payment->getNumber();
        $details['PAYMENTREQUEST_0_CURRENCYCODE'] = $this->currency;
        $details['PAYMENTREQUEST_0_AMT'] = $this->format($payment->getAmount());

        $this->addSaleDetails($details, $payment->getSale());

        $request->setResult($details);
    }

    /**
     * Adds the sale details.
     *
     * @param array               $details
     * @param Model\SaleInterface $sale
     */
    private function addSaleDetails(array &$details, Model\SaleInterface $sale)
    {
        if ($sale->getCurrency()->getCode() !== $this->currency) {
            return;
        }

        if (!$sale->getGrandTotal()->equals($details['PAYMENTREQUEST_0_AMT'])) {
            return;
        }

        $this->getCalculator()->calculateSale($sale);

        $this->line = 0;
        $lineTotals = new Decimal(0);

        // Items
        foreach ($sale->getItems() as $item) {
            $lineTotals += $this->addItemDetails($details, $item);
        }

        // Discounts
        foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $discount) {
            $lineTotals += $this->addDiscountDetails($details, $discount);
        }

        // Lines total
        $details['PAYMENTREQUEST_0_ITEMAMT'] = $this->format($lineTotals);

        // Shipping
        $result = $this->getCalculator()->calculateSaleShipment($sale);
        $details['PAYMENTREQUEST_0_SHIPPINGAMT'] = $this->format($result->getTotal());

        // Taxes
        //$details['PAYMENTREQUEST_0_TAXAMT'] = $this->format($sale->getFinalResult()->getTax());
    }

    /**
     * Builds the item details.
     */
    private function addItemDetails(array &$details, Model\SaleItemInterface $item): Decimal
    {
        $total = new Decimal(0);

        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $itemResult = $this->getCalculator()->calculateSaleItem($item);

            $details['L_PAYMENTREQUEST_0_NAME' . $this->line] = $item->getTotalQuantity() . 'x '
                . $item->getDesignation();
            $details['L_PAYMENTREQUEST_0_NUMBER' . $this->line] = $item->getReference();
            if (!empty($description = implode('. ', $item->getDescriptions()))) {
                $details['L_PAYMENTREQUEST_0_DESC' . $this->line] = $description;
            }
            $details['L_PAYMENTREQUEST_0_AMT' . $this->line] = $this->format($itemResult->getTotal());
            //$details['L_PAYMENTREQUEST_0_TAXAMT' . $this->line] = $this->format($itemResult->getTax());
            //$details['L_PAYMENTREQUEST_0_ITEMURL' . $this->itemNum] = '';

            $total = $itemResult->getTotal();

            $this->line++;
        }

        foreach ($item->getChildren() as $child) {
            $total += $this->addItemDetails($details, $child);
        }

        return $total;
    }

    /**
     * Adds the discount details.
     */
    private function addDiscountDetails(array &$details, Model\SaleAdjustmentInterface $discount): Decimal
    {
        $discountResult = $this->getCalculator()->calculateSaleDiscount($discount);

        $details['L_PAYMENTREQUEST_0_NAME' . $this->line] = $discount->getDesignation();
        $details['L_PAYMENTREQUEST_0_AMT' . $this->line] = '-' . $this->format($discountResult->getTotal());
        //$details['L_PAYMENTREQUEST_0_TAXAMT' . $this->line] = '-' . $this->format($discountResult->getTax());

        $this->line++;

        return $discountResult->getTotal()->negate();
    }

    /**
     * Formats the given amount.
     *
     * @param Decimal $amount
     *
     * @return string
     */
    private function format(Decimal $amount): string
    {
        return Money::fixed($amount, $this->currency);
    }

    private function getCalculator(): AmountCalculatorInterface
    {
        if ($this->calculator) {
            return $this->calculator;
        }

        return $this->calculator = $this->calculatorFactory->create($this->currency);
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() == 'array';
    }
}
