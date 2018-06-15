<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class SaleCopier
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCopier implements SaleCopierInterface
{
    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

    /**
     * @var Model\SaleInterface
     */
    protected $source;

    /**
     * @var Model\SaleInterface
     */
    protected $target;

    /**
     * @var PropertyAccessorInterface
     */
    protected $accessor;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface $saleFactory
     * @param Model\SaleInterface  $source
     * @param Model\SaleInterface  $target
     */
    public function __construct(
        SaleFactoryInterface $saleFactory,
        Model\SaleInterface $source,
        Model\SaleInterface $target
    ) {
        $this->saleFactory = $saleFactory;
        $this->source = $source;
        $this->target = $target;

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @inheritdoc
     */
    public function copySale()
    {
        $this
            ->copyData()
            ->copyAddresses()
            ->copyAttachments()
            ->copyNotifications()
            ->copyItems()
            ->copyAdjustments()
            ->copyPayments();
    }

    /**
     * @inheritdoc
     */
    public function copyData()
    {
        $fields = [
            'currency', 'customer', 'customerGroup',
            'sameAddress', 'shipmentMethod', 'shipmentAmount', 'relayPoint',
            'vatDisplayMode', 'autoDiscount', 'taxExempt', 'depositTotal', 'grandTotal',
            'paymentTerm', 'outstandingDate', 'outstandingLimit',
            'voucherNumber', 'description', 'comment', 'documentComment', 'acceptedAt',
        ];

        // Copy information fields only if source has no customer entity
        if (null === $this->source->getCustomer()) {
            array_push($fields, 'email', 'company', 'gender', 'firstName', 'lastName');
        }

        $this->copy($this->source, $this->target, $fields);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function copyAddresses()
    {
        // Invoice address
        if (null !== $sourceInvoiceAddress = $this->source->getInvoiceAddress()) {
            $targetInvoiceAddress = $this->saleFactory->createAddressForSale($this->target, $sourceInvoiceAddress);
            $this->target->setInvoiceAddress($targetInvoiceAddress);
        }

        // Delivery address
        if (null !== $sourceDeliveryAddress = $this->source->getDeliveryAddress()) {
            $targetDeliveryAddress = $this->saleFactory->createAddressForSale($this->target, $sourceDeliveryAddress);
            $this->target->setDeliveryAddress($targetDeliveryAddress);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function copyAdjustments()
    {
        foreach ($this->source->getAdjustments() as $sourceAdjustment) {
            $targetAdjustment = $this->saleFactory->createAdjustmentForSale($this->target);
            $this->target->addAdjustment($targetAdjustment);
            $this->copyAdjustment($sourceAdjustment, $targetAdjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function copyAttachments()
    {
        foreach ($this->source->getAttachments() as $sourceAttachment) {
            $targetAttachment = $this->saleFactory->createAttachmentForSale($this->target);
            $this->target->addAttachment($targetAttachment);
            $this->copyAttachment($sourceAttachment, $targetAttachment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function copyNotifications()
    {
        foreach ($this->source->getNotifications() as $sourceNotification) {
            $targetNotification = $this->saleFactory->createNotificationForSale($this->target);
            $this->target->addNotification($targetNotification);
            $this->copyNotification($sourceNotification, $targetNotification);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function copyItems()
    {
        foreach ($this->source->getItems() as $sourceItem) {
            $targetItem = $this->saleFactory->createItemForSale($this->target);
            $this->target->addItem($targetItem);
            $this->copyItem($sourceItem, $targetItem);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function copyPayments()
    {
        foreach ($this->source->getPayments() as $sourcePayment) {
            $targetPayment = $this->saleFactory->createPaymentForSale($this->target);
            $this->target->addPayment($targetPayment);
            $this->copyPayment($sourcePayment, $targetPayment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    /*public function copyShipments()
    {
        if (!$this->source instanceof ShipmentSubjectInterface) {
            return $this;
        }
        if (!$this->target instanceof ShipmentSubjectInterface) {
            return $this;
        }

        foreach ($this->source->getShipments() as $sourceShipment) {
            $targetShipment = $this->saleFactory->createShipmentForSale($this->target);
            $this->target->addShipment($targetShipment);
            $this->copyShipment($sourceShipment, $targetShipment);
        }

        return $this;
    }*/

    /**
     * @inheritdoc
     */
    /*public function copyInvoices()
    {
        if (!$this->source instanceof InvoiceSubjectInterface) {
            return $this;
        }
        if (!$this->target instanceof InvoiceSubjectInterface) {
            return $this;
        }

        foreach ($this->source->getPayments() as $sourceInvoice) {
            $targetInvoice = $this->saleFactory->createInvoiceForSale($this->target);
            $this->target->addInvoice($targetInvoice);
            $this->copyInvoice($sourceInvoice, $targetInvoice);
        }

        return $this;
    }*/

    /**
     * Copies the source adjustment into the target adjustment.
     *
     * @param Model\AdjustmentInterface $source
     * @param Model\AdjustmentInterface $target
     */
    private function copyAdjustment(Model\AdjustmentInterface $source, Model\AdjustmentInterface $target)
    {
        $this->copy($source, $target, [
            'designation', 'type', 'mode', 'amount', 'immutable',
        ]);
    }

    /**
     * Copies the source attachment into the target attachment.
     *
     * @param Model\SaleAttachmentInterface $source
     * @param Model\SaleAttachmentInterface $target
     */
    private function copyAttachment(Model\SaleAttachmentInterface $source, Model\SaleAttachmentInterface $target)
    {
        $this->copy($source, $target, [
            'path', 'title', 'type', 'size', 'internal', 'createdAt', 'updatedAt',
        ]);
    }

    /**
     * Copies the source notification into the target notification.
     *
     * @param Model\SaleNotificationInterface $source
     * @param Model\SaleNotificationInterface $target
     */
    private function copyNotification(Model\SaleNotificationInterface $source, Model\SaleNotificationInterface $target)
    {
        $this->copy($source, $target, [
            'type', 'data', 'sentAt', 'details'
        ]);
    }

    /**
     * Copy the source item into the target item.
     *
     * @param Model\SaleItemInterface $source
     * @param Model\SaleItemInterface $target
     */
    private function copyItem(Model\SaleItemInterface $source, Model\SaleItemInterface $target)
    {
        $this->copy($source, $target, [
            'designation', 'description', 'reference', 'taxGroup', 'netPrice', 'weight', 'quantity',
            'position', 'compound', 'immutable', 'configurable', 'private', 'data',
        ]);

        // SubjectIdentity
        $this->copy($source->getSubjectIdentity(), $target->getSubjectIdentity(), [
            'provider', 'identifier',
        ]);

        // Adjustments
        foreach ($source->getAdjustments() as $sourceAdjustment) {
            $targetAdjustment = $this->saleFactory->createAdjustmentForItem($target);
            $target->addAdjustment($targetAdjustment);
            $this->copyAdjustment($sourceAdjustment, $targetAdjustment);
        }

        // Children
        foreach ($source->getChildren() as $sourceChild) {
            $targetChild = $this->saleFactory->createItemForSale($target->getSale());
            $target->addChild($targetChild);
            $this->copyItem($sourceChild, $targetChild);
        }
    }

    /**
     * Copy the source payment into the target payment.
     *
     * @param PaymentInterface $source
     * @param PaymentInterface $target
     */
    private function copyPayment(PaymentInterface $source, PaymentInterface $target)
    {
        $this->copy($source, $target, [
            'currency', 'method', 'key', 'number', 'amount', 'state', 'details',
            'description', 'createdAt', 'updatedAt', 'completedAt',
        ]);
    }

    /**
     * Copies the given properties from the source object to the target object.
     *
     * @param object $source
     * @param object $target
     * @param array $properties
     */
    private function copy($source, $target, array $properties)
    {
        $properties = (array)$properties;

        foreach ($properties as $property) {
            $this->accessor->setValue($target, $property, $this->accessor->getValue($source, $property));
        }
    }
}
