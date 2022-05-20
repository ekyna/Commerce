<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class SaleCopier
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * Must be kept in sync with all mapped properties of AbstractSale.
 * @see     \Ekyna\Component\Commerce\Common\Entity\AbstractSale
 *
 */
class SaleCopier implements SaleCopierInterface
{
    /**
     * @var FactoryHelperInterface
     */
    protected $factoryHelper;

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

    public function __construct(
        FactoryHelperInterface $factoryHelper,
        Model\SaleInterface    $source,
        Model\SaleInterface    $target
    ) {
        $this->factoryHelper = $factoryHelper;
        $this->source = $source;
        $this->target = $target;

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function copyData()
    {
        $fields = [
            'currency',
            'customer',
            'customerGroup',
            'sameAddress',
            'coupon',
            'couponData',
            'relayPoint',
            'shipmentMethod',
            'shipmentAmount',
            'shipmentWeight',
            'shipmentLabel',
            'autoShipping',
            'autoDiscount',
            'autoNotify',
            'taxExempt',
            'vatDisplayMode',
            'depositTotal',
            'grandTotal', // TODO Remove as calculated
            'paymentMethod',
            'paymentTerm',
            'outstandingDate',
            'outstandingLimit',
            'title',
            'voucherNumber',
            'description',
            'preparationNote',
            'comment',
            'documentComment',
            'exchangeRate',
            'exchangeDate',
            'locale',
            'acceptedAt',
            'source',
        ];

        // Copy information fields only if source has no customer entity
        if (null === $this->source->getCustomer()) {
            array_push($fields, 'email', 'company', 'gender', 'firstName', 'lastName');
        }

        $this->copy($this->source, $this->target, $fields);

        if ($this->source instanceof OrderInterface) {
            if ($this->target instanceof OrderInterface) {
                $this->target->setOriginCustomer($this->source->getOriginCustomer());
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function copyAddresses()
    {
        // Invoice address
        if ($sourceInvoiceAddress = $this->source->getInvoiceAddress()) {
            $targetInvoiceAddress = $this->factoryHelper->createAddressForSale($this->target, $sourceInvoiceAddress);
            $this->target->setInvoiceAddress($targetInvoiceAddress);
        }

        // Delivery address
        if ($sourceDeliveryAddress = $this->source->getDeliveryAddress()) {
            $targetDeliveryAddress = $this->factoryHelper->createAddressForSale($this->target, $sourceDeliveryAddress);
            $this->target->setDeliveryAddress($targetDeliveryAddress);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function copyAdjustments()
    {
        foreach ($this->source->getAdjustments() as $sourceAdjustment) {
            $targetAdjustment = $this->factoryHelper->createAdjustmentForSale($this->target);
            $this->target->addAdjustment($targetAdjustment);
            $this->copyAdjustment($sourceAdjustment, $targetAdjustment);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function copyAttachments()
    {
        foreach ($this->source->getAttachments() as $sourceAttachment) {
            $targetAttachment = $this->factoryHelper->createAttachmentForSale($this->target);
            $this->target->addAttachment($targetAttachment);
            $this->copyAttachment($sourceAttachment, $targetAttachment);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function copyNotifications()
    {
        foreach ($this->source->getNotifications() as $sourceNotification) {
            $targetNotification = $this->factoryHelper->createNotificationForSale($this->target);
            $this->target->addNotification($targetNotification);
            $this->copyNotification($sourceNotification, $targetNotification);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function copyItems()
    {
        foreach ($this->source->getItems() as $sourceItem) {
            $targetItem = $this->factoryHelper->createItemForSale($this->target);
            $this->target->addItem($targetItem);
            $this->copyItem($sourceItem, $targetItem);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function copyPayments()
    {
        foreach ($this->source->getPayments() as $sourcePayment) {
            $targetPayment = $this->factoryHelper->createPaymentForSale($this->target);
            $this->target->addPayment($targetPayment);
            $this->copyPayment($sourcePayment, $targetPayment);
        }

        return $this;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
            'designation',
            'type',
            'mode',
            'amount',
            'immutable',
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
            'path',
            'title',
            'type',
            'size',
            'internal',
            'createdAt',
            'updatedAt',
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
            'type',
            'data',
            'sentAt',
            'details',
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
            'designation',
            'description',
            'reference',
            'taxGroup',
            'netPrice',
            'weight',
            'quantity',
            'position',
            'compound',
            'immutable',
            'configurable',
            'private',
            'data',
        ]);

        // SubjectIdentity
        $this->copy($source->getSubjectIdentity(), $target->getSubjectIdentity(), [
            'provider',
            'identifier',
        ]);

        // Adjustments
        foreach ($source->getAdjustments() as $sourceAdjustment) {
            $targetAdjustment = $this->factoryHelper->createAdjustmentForItem($target);
            $target->addAdjustment($targetAdjustment);
            $this->copyAdjustment($sourceAdjustment, $targetAdjustment);
        }

        // Children
        foreach ($source->getChildren() as $sourceChild) {
            $targetChild = $this->factoryHelper->createItemForSale($target->getRootSale());
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
            'refund',
            'currency',
            'method',
            'key',
            'number',
            'amount',
            'realAmount',
            'state',
            'details',
            'description',
            'createdAt',
            'updatedAt',
            'completedAt',
            'exchangeRate',
            'exchangeDate',
        ]);
    }

    /**
     * Copies the given properties from the source object to the target object.
     *
     * @param object $source
     * @param object $target
     * @param array  $properties
     */
    private function copy($source, $target, array $properties)
    {
        foreach ($properties as $property) {
            $this->accessor->setValue($target, $property, $this->accessor->getValue($source, $property));
        }
    }
}
