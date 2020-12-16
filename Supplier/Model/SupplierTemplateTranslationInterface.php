<?php

namespace Ekyna\Component\Commerce\Supplier\Model;


use Ekyna\Component\Commerce\Supplier\Entity\SupplierTemplateTranslation;
use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface SupplierTemplateTranslationInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierTemplateTranslationInterface extends TranslationInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int;

    /**
     * Returns the subject.
     *
     * @return string
     */
    public function getSubject(): ?string;

    /**
     * Sets the subject.
     *
     * @param string $subject
     *
     * @return SupplierTemplateTranslation
     */
    public function setSubject(string $subject): SupplierTemplateTranslation;

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage(): ?string;

    /**
     * Sets the message.
     *
     * @param string $message
     *
     * @return SupplierTemplateTranslation
     */
    public function setMessage(string $message): SupplierTemplateTranslation;
}
