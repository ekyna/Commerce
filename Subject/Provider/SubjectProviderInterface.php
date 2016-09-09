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
     *
     * @return mixed
     */
    public function handleChoiceSubmit(SaleItemInterface $item);

    /**
     * Returns whether the subject configuration is needed or not.
     *
     * @param SaleItemInterface $item
     *
     * @return bool
     */
    public function needConfiguration(SaleItemInterface $item);

    /**
     * Builds the subject configuration form.
     *
     * @param FormInterface     $form
     * @param SaleItemInterface $item
     */
    public function buildConfigurationForm(FormInterface $form, SaleItemInterface $item);

    /**
     * Configuration form submit handler.
     *
     * @param SaleItemInterface $item
     *
     * @return mixed
     */
    public function handleConfigurationSubmit(SaleItemInterface $item);

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
