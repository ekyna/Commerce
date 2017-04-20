<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractMethodTranslation;

/**
 * Class PaymentMethodTranslation
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodTranslation extends AbstractMethodTranslation
{
    private ?string $notice = null;
    private ?string $footer = null;

    public function getNotice(): ?string
    {
        return $this->notice;
    }

    public function setNotice(?string $notice): PaymentMethodTranslation
    {
        $this->notice = $notice;

        return $this;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function setFooter(?string $footer): PaymentMethodTranslation
    {
        $this->footer = $footer;

        return $this;
    }
}
