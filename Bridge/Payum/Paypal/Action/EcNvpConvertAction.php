<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action;

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

/**
 * Class EcNvpConvertAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EcNvpConvertAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var AmountCalculatorInterface
     */
    private $calculator;

    /**
     * @var string
     */
    private $brandName;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $line;


    /**
     * Constructor.
     *
     * @param AmountCalculatorInterface $calculator
     * @param string                    $brandName
     */
    public function __construct(AmountCalculatorInterface $calculator, $brandName = null)
    {
        $this->calculator = $calculator;
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

        if (0 !== Money::compare($sale->getGrandTotal(), $details['PAYMENTREQUEST_0_AMT'], $this->currency)) {
            return;
        }

        $this->calculator->calculateSale($sale, $this->currency);

        $this->line = 0;
        $lineTotals = 0;

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
        $details['PAYMENTREQUEST_0_SHIPPINGAMT'] = $this->format($sale->getShipmentResult($this->currency)->getTotal());

        // Taxes
        //$details['PAYMENTREQUEST_0_TAXAMT'] = $this->format($sale->getFinalResult()->getTax());
    }

    /**
     * Builds the item details.
     *
     * @param array                   $details
     * @param Model\SaleItemInterface $item
     *
     * @return float
     */
    private function addItemDetails(array &$details, Model\SaleItemInterface $item)
    {
        $total = 0;

        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $itemResult = $item->getResult($this->currency);

            $details['L_PAYMENTREQUEST_0_NAME' . $this->line] = $item->getTotalQuantity() . 'x ' . $item->getDesignation();
            $details['L_PAYMENTREQUEST_0_NUMBER' . $this->line] = $item->getReference();
            if (!empty($description = $item->getDescription())) {
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
     *
     * @param array                         $details
     * @param Model\SaleAdjustmentInterface $discount
     *
     * @return float
     */
    private function addDiscountDetails(array &$details, Model\SaleAdjustmentInterface $discount)
    {
        $discountResult = $discount->getResult($this->currency);

        $details['L_PAYMENTREQUEST_0_NAME' . $this->line] = $discount->getDesignation();
        $details['L_PAYMENTREQUEST_0_AMT' . $this->line] = '-' . $this->format($discountResult->getTotal());
        //$details['L_PAYMENTREQUEST_0_TAXAMT' . $this->line] = '-' . $this->format($discountResult->getTax());

        $this->line++;

        return -$discountResult->getTotal();
    }

    /**
     * Formats the given amount.
     *
     * @param float $amount
     *
     * @return string
     */
    private function format($amount)
    {
        return (string)Money::round($amount, $this->currency);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() == 'array';
    }
}
