<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Interface SubjectProviderInterface
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectProviderInterface
{
    /**
     * Builds the subject choice form.
     *
     * @param FormInterface $form
     */
    public function buildChoiceForm(FormInterface $form);

    /**
     * Builds the sale item form.
     *
     * @param FormInterface $form
     */
    public function buildItemForm(FormInterface $form);

    /**
     * Sets the item default data from the given subject.
     *
     * @param SaleItemInterface $item
     * @param mixed             $subject
     */
    public function setItemDefaults(SaleItemInterface $item, $subject);

    /**
     * Returns whether the provider supports the given subject or not.
     *
     * @param mixed $subject
     *
     * @return bool
     */
    public function supports($subject);

    /**
     * Returns the provider name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the subject type label.
     *
     * @return string
     */
    public function getLabel();
}
