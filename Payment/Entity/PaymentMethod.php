<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractMethod;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\MessageInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;

/**
 * Class PaymentMethod
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethod extends AbstractMethod implements PaymentMethodInterface
{
    /**
     * @var ArrayCollection|CurrencyInterface[]
     */
    protected $currencies;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->currencies = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function hasCurrencies()
    {
        return 0 < $this->currencies->count();
    }

    /**
     * @inheritdoc
     */
    public function hasCurrency(CurrencyInterface $currency)
    {
        return $this->currencies->contains($currency);
    }

    /**
     * @inheritdoc
     */
    public function addCurrency(CurrencyInterface $currency)
    {
        if (!$this->hasCurrency($currency)) {
            $this->currencies->add($currency);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCurrency(CurrencyInterface $currency)
    {
        if ($this->hasCurrency($currency)) {
            $this->currencies->removeElement($currency);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @inheritdoc
     */
    public function isManual()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isCredit()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isOutstanding()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function validateMessageClass(MessageInterface $message)
    {
        if (!$message instanceof PaymentMessage) {
            throw new InvalidArgumentException("Expected instance of PaymentMessage.");
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return PaymentMethodTranslation::class;
    }
}
