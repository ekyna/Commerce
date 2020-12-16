<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class SupplierTemplateTranslation
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierTemplateTranslation extends AbstractTranslation implements SupplierTemplateTranslationInterface
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $message;


    /**
     * @inheritDoc
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @inheritDoc
     */
    public function setSubject(string $subject): SupplierTemplateTranslation
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function setMessage(string $message): SupplierTemplateTranslation
    {
        $this->message = $message;

        return $this;
    }
}
