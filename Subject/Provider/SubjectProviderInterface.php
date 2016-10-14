<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Interface SubjectProviderInterface
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectProviderInterface
{
    const DATA_KEY = 'provider';

    // TODO Move SaleItemInterface management methods to a dedicated service

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
     * Returns the subject from the relative.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return mixed
     */
    public function resolve(SubjectRelativeInterface $relative);

    /**
     * Returns whether the resolver supports the relative or not.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return boolean
     */
    public function supportsRelative(SubjectRelativeInterface $relative);

    /**
     * Returns whether the provider supports the subject or not.
     *
     * @param mixed $subject
     *
     * @return bool
     */
    public function supportsSubject($subject);

    /**
     * Returns the subject stock unit repository.
     *
     * @return StockUnitRepositoryInterface
     */
    public function getStockUnitRepository();

    /**
     * Returns the stock unit change event name.
     *
     * @return string
     */
    public function getStockUnitChangeEventName();

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
