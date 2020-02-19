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
 *
 * @method PaymentMethodTranslation translate($locale = null, $create = false)
 * @method ArrayCollection|PaymentMethodTranslation[] getTranslations()
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
     * @var bool
     */
    protected $private;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->currencies = new ArrayCollection();
        $this->defaultCurrency = true;
        $this->private = false;
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
     * @inheritdoc
     */
    public function isDefaultCurrency(): bool
    {
        return $this->defaultCurrency;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultCurrency(bool $default): PaymentMethodInterface
    {
        $this->defaultCurrency = $default;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @inheritdoc
     */
    public function setPrivate(bool $private): PaymentMethodInterface
    {
        $this->private = $private;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isManual(): bool
    {
        return false; // TODO Method should be abstract
    }

    /**
     * @inheritdoc
     */
    public function isCredit(): bool
    {
        return false; // TODO Method should be abstract
    }

    /**
     * @inheritdoc
     */
    public function isOutstanding(): bool
    {
        return false; // TODO Method should be abstract
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

    /**
     * @inheritdoc
     */
    public function getNotice(): ?string
    {
        return $this->translate()->getNotice();
    }

    /**
     * @inheritdoc
     */
    public function getMention(): ?string
    {
        return $this->translate()->getMention();
    }

    /**
     * @inheritdoc
     */
    public function getFooter(): ?string
    {
        return $this->translate()->getFooter();
    }
}
