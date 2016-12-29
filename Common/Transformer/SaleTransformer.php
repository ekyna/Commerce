<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class SaleTransformer
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformer implements SaleTransformerInterface
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
        $this->copy($source, $target, [
            'currency', 'customer', 'customerGroup',
            'email', 'company', 'gender', 'firstName', 'lastName',
            'sameAddress', 'preferredShipmentMethod', 'shipmentAmount'
        ]);

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
            $target->addItem($targetItem); // So that the sale is available for listeners
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
        /*foreach ($source->getShipments() as $sourceShipment) {
            $targetShipment = $this->saleFactory->createShipmentForSale($target);
            $target->addShipment($targetShipment);
            $this->copyShipment($sourceShipment, $targetShipment);
        }*/
    }

    /**
     * @inheritdoc
     */
    public function copyAddress(Model\AddressInterface $source, Model\AddressInterface $target)
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
            'path', 'size', 'internal'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function copyAdjustment(Model\AdjustmentInterface $source, Model\AdjustmentInterface $target)
    {
        $this->copy($source, $target, [
            'designation', 'type', 'mode', 'amount', 'immutable'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function copyPayment(PaymentInterface $source, PaymentInterface $target)
    {
        $this->copy($source, $target, [
            //'number', // TODO This is a problem
            'currency', 'method', 'amount', 'state',
            'details', 'description', 'completedAt'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function copyItem(Model\SaleItemInterface $source, Model\SaleItemInterface $target)
    {
        $this->copy($source, $target, [
            'designation', 'reference', 'netPrice', 'weight',
            'quantity', 'position', 'immutable', 'configurable',
            'subjectData',
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
            $target->addChild($targetChild); // So that the sale is accessible
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
