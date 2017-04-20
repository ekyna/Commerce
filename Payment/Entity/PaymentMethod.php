<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Entity\AbstractMethod;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\MentionSubjectTrait;
use Ekyna\Component\Commerce\Common\Model\MessageInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;

/**
 * Class PaymentMethod
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method PaymentMethodTranslation translate($locale = null, $create = false)
 * @method Collection|PaymentMethodTranslation[] getTranslations()
 */
class PaymentMethod extends AbstractMethod implements PaymentMethodInterface
{
    use MentionSubjectTrait;

    /** @var Collection|CurrencyInterface[] */
    protected Collection $currencies;
    protected bool       $defaultCurrency = true;
    protected bool       $private         = false;


    public function __construct()
    {
        parent::__construct();

        $this->initializeMentions();

        $this->currencies = new ArrayCollection();
    }

    public function hasCurrencies(): bool
    {
        return 0 < $this->currencies->count();
    }

    public function hasCurrency(CurrencyInterface $currency): bool
    {
        return $this->currencies->contains($currency);
    }

    public function addCurrency(CurrencyInterface $currency): PaymentMethodInterface
    {
        if (!$this->hasCurrency($currency)) {
            $this->currencies->add($currency);
        }

        return $this;
    }

    public function removeCurrency(CurrencyInterface $currency): PaymentMethodInterface
    {
        if ($this->hasCurrency($currency)) {
            $this->currencies->removeElement($currency);
        }

        return $this;
    }

    public function getCurrencies(): Collection
    {
        return $this->currencies;
    }

    public function isDefaultCurrency(): bool
    {
        return $this->defaultCurrency;
    }

    public function setDefaultCurrency(bool $default): PaymentMethodInterface
    {
        $this->defaultCurrency = $default;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): PaymentMethodInterface
    {
        $this->private = $private;

        return $this;
    }

    public function isManual(): bool
    {
        return false; // TODO Method should be abstract
    }

    public function isCredit(): bool
    {
        return false; // TODO Method should be abstract
    }

    public function isOutstanding(): bool
    {
        return false; // TODO Method should be abstract
    }

    public function isFactor(): bool
    {
        return false; // TODO Method should be abstract
    }

    public function hasMention(PaymentMethodMention $mention): bool
    {
        return $this->mentions->contains($mention);
    }

    public function addMention(PaymentMethodMention $mention): PaymentMethodInterface
    {
        if ($this->hasMention($mention)) {
            return $this;
        }

        $this->mentions->add($mention);
        $mention->setMethod($this);

        return $this;
    }

    public function removeMention(PaymentMethodMention $mention): PaymentMethodInterface
    {
        if (!$this->hasMention($mention)) {
            return $this;
        }

        $this->mentions->removeElement($mention);
        $mention->setMethod(null);

        return $this;
    }

    protected function validateMessageClass(MessageInterface $message): void
    {
        if (!$message instanceof PaymentMessage) {
            throw new UnexpectedTypeException($message, PaymentMessage::class);
        }
    }

    protected function getTranslationClass(): string
    {
        return PaymentMethodTranslation::class;
    }

    public function getNotice(): ?string
    {
        return $this->translate()->getNotice();
    }

    public function getFooter(): ?string
    {
        return $this->translate()->getFooter();
    }
}
