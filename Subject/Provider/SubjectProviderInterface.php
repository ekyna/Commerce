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
    const DATA_KEY = 'provider';

    /**
     * Returns whether the subject choice is needed or not.
     *
     * @param SaleItemInterface $item
     *
     * @return bool
     */
    public function needChoice(SaleItemInterface $item);

    /**
     * Builds the subject choice form.
     *
     * @param FormInterface $form
     */
    public function buildChoiceForm(FormInterface $form);

    /**
     * Choice form submit handler.
     *
     * @param SaleItemInterface $item
     */
    public function handleChoiceSubmit(SaleItemInterface $item);

    /**
     * Prepares the item (assign subjects recursively).
     *
     * @param SaleItemInterface $item
     */
    public function prepareItem(SaleItemInterface $item);

    /**
     * Builds the subject item form.
     *
     * @param FormInterface $form
     * @param SaleItemInterface $item
     */
    public function buildItemForm(FormInterface $form, SaleItemInterface $item);

    /**
     * Item form submit handler.
     *
     * @param SaleItemInterface $item
     */
    public function handleItemSubmit(SaleItemInterface $item);

    /**
     * Returns the subject from the sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return mixed
     */
    public function resolve(SaleItemInterface $item);

    /**
     * Returns whether the resolver supports the sale item or not.
     *
     * @param SaleItemInterface $item
     *
     * @return boolean
     */
    public function supportsItem(SaleItemInterface $item);

    /**
     * Returns whether the provider supports the subject or not.
     *
     * @param mixed $subject
     *
     * @return bool
     */
    public function supportsSubject($subject);

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
