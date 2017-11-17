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
     * @var PropertyAccessorInterface
     */
    protected $accessor;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface $saleFactory
     */
    public function __construct(SaleFactoryInterface $saleFactory)
    {
        $this->saleFactory = $saleFactory;

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @inheritdoc
     */
    public function copySale(Model\SaleInterface $source, Model\SaleInterface $target)
    {
        $fields = [
            'currency', 'customer', 'customerGroup',
            'sameAddress', 'preferredShipmentMethod', 'shipmentAmount',
            'taxExempt', 'paymentTerm', 'outstandingDate', 'outstandingLimit',
            'voucherNumber', 'description', 'comment',
        ];

        // Copy information fields only if source has no customer entity
        if (null === $source->getCustomer()) {
            array_push($fields, 'email', 'company', 'gender', 'firstName', 'lastName');
        }

        $this->copy($source, $target, $fields);

        // Invoice address
        if (null !== $sourceInvoiceAddress = $source->getInvoiceAddress()) {
            $targetInvoiceAddress = $this->saleFactory->createAddressForSale($target, $sourceInvoiceAddress);
            $target->setInvoiceAddress($targetInvoiceAddress);
        }

        // Delivery address
        if (null !== $sourceDeliveryAddress = $source->getDeliveryAddress()) {
            $targetDeliveryAddress = $this->saleFactory->createAddressForSale($target, $sourceDeliveryAddress);
            $target->setDeliveryAddress($targetDeliveryAddress);
        }

        // Attachments
        foreach ($source->getAttachments() as $sourceAttachment) {
            $targetAttachment = $this->saleFactory->createAttachmentForSale($target);
            $target->addAttachment($targetAttachment);
            $this->copyAttachment($sourceAttachment, $targetAttachment);
        }

        // Items
        foreach ($source->getItems() as $sourceItem) {
            $targetItem = $this->saleFactory->createItemForSale($target);
            $target->addItem($targetItem);
            $this->copyItem($sourceItem, $targetItem);
        }

        // Adjustments
        foreach ($source->getAdjustments() as $sourceAdjustment) {
            $targetAdjustment = $this->saleFactory->createAdjustmentForSale($target);
            $target->addAdjustment($targetAdjustment);
            $this->copyAdjustment($sourceAdjustment, $targetAdjustment);
        }

        // Payments
        foreach ($source->getPayments() as $sourcePayment) {
            $targetPayment = $this->saleFactory->createPaymentForSale($target);
            $target->addPayment($targetPayment);
            $this->copyPayment($sourcePayment, $targetPayment);
        }

        // Shipments
        /*if ($source instanceof ShipmentSubjectInterface && $target instanceof ShipmentSubjectInterface) {
            foreach ($source->getShipments() as $sourceShipment) {
                $targetShipment = $this->saleFactory->createShipmentForSale($target);
                $target->addShipment($targetShipment);
                $this->copyShipment($sourceShipment, $targetShipment);
            }
        }

        // Invoices
        if ($source instanceof InvoiceSubjectInterface && $target instanceof InvoiceSubjectInterface) {
            foreach ($source->getInvoices() as $sourceInvoice) {
                $targetInvoice = $this->saleFactory->createInvoiceForSale($target);
                $target->addInvoice($targetInvoice);
                $this->copyInvoice($sourceInvoice, $targetInvoice);
            }
        }*/
    }

    /**
     * @inheritdoc
     */
    public function copyAddress(Model\SaleAddressInterface $source, Model\SaleAddressInterface $target)
    {
        $this->copy($source, $target, [
            'company', 'gender', 'firstName', 'lastName',
            'street', 'supplement', 'postalCode', 'city',
            'country', 'state', 'phone', 'mobile',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function copyAttachment(Model\SaleAttachmentInterface $source, Model\SaleAttachmentInterface $target)
    {
        $this->copy($source, $target, [
            'path', 'title', 'size', 'internal', 'createdAt', 'updatedAt',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function copyAdjustment(Model\AdjustmentInterface $source, Model\AdjustmentInterface $target)
    {
        $this->copy($source, $target, [
            'designation', 'type', 'mode', 'amount', 'immutable',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function copyPayment(PaymentInterface $source, PaymentInterface $target)
    {
        $this->copy($source, $target, [
            'currency', 'method', 'amount', 'state', 'details',
            'description', 'createdAt', 'updatedAt', 'completedAt',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function copyItem(Model\SaleItemInterface $source, Model\SaleItemInterface $target)
    {
        $this->copy($source, $target, [
            'designation', 'reference', 'netPrice', 'weight', 'taxGroup', 'quantity',
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
     * @inheritdoc
     */
    public function copy($source, $target, $properties)
    {
        $properties = (array)$properties;

        foreach ($properties as $property) {
            $this->accessor->setValue($target, $property, $this->accessor->getValue($source, $property));
        }
    }
}
