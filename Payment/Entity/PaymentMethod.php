<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var bool
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->currencies = new ArrayCollection();
        $this->defaultCurrency = true;
    }

    /**
     * @inheritdoc
     */
    public function hasCurrencies(): bool
    {
        return 0 < $this->currencies->count();
    }

    /**
     * @inheritdoc
     */
    public function hasCurrency(CurrencyInterface $currency): bool
    {
        return $this->currencies->contains($currency);
    }

    /**
     * @inheritdoc
     */
    public function addCurrency(CurrencyInterface $currency): PaymentMethodInterface
    {
        if (!$this->hasCurrency($currency)) {
            $this->currencies->add($currency);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCurrency(CurrencyInterface $currency): PaymentMethodInterface
    {
        if ($this->hasCurrency($currency)) {
            $this->currencies->removeElement($currency);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrencies(): Collection
    {
        return $this->currencies;
    }

    /**
     * Returns whether to use the default currency.
     *
     * @return bool
     */
    public function isDefaultCurrency(): bool
    {
        return $this->defaultCurrency;
    }

    /**
     * Sets whether to use the default currency.
     *
     * @param bool $default
     *
     * @return PaymentMethod
     */
    public function setDefaultCurrency(bool $default): PaymentMethodInterface
    {
        $this->defaultCurrency = $default;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isManual(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isCredit(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isOutstanding(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function validateMessageClass(MessageInterface $message): void
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
