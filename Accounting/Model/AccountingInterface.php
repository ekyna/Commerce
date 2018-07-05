<?php

namespace Ekyna\Component\Commerce\Accounting\Model;

use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Resource\Model\IsEnabledInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface AccountInterface
 * @package Ekyna\Component\Commerce\Accounting\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AccountingInterface extends SortableInterface, IsEnabledInterface, ResourceInterface
{
    /**
     * Returns the number.
     *
     * @return string
     */
    public function getNumber();

    /**
     * Sets the number.
     *
     * @param string $number
     *
     * @return $this|AccountingInterface
     */
    public function setNumber($number);

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|AccountingInterface
     */
    public function setName($name);

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|AccountingInterface
     */
    public function setType($type);

    /**
     * Returns the taxRule.
     *
     * @return TaxRuleInterface
     */
    public function getTaxRule();

    /**
     * Sets the taxRule.
     *
     * @param TaxRuleInterface $taxRule
     *
     * @return $this|AccountingInterface
     */
    public function setTaxRule(TaxRuleInterface $taxRule = null);

    /**
     * Returns the tax.
     *
     * @return TaxInterface
     */
    public function getTax();

    /**
     * Sets the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|AccountingInterface
     */
    public function setTax(TaxInterface $tax = null);

    /**
     * Returns the payment method.
     *
     * @return PaymentMethodInterface
     */
    public function getPaymentMethod();

    /**
     * Sets the payment method.
     *
     * @param PaymentMethodInterface $method
     *
     * @return $this|AccountingInterface
     */
    public function setPaymentMethod(PaymentMethodInterface $method = null);
}
