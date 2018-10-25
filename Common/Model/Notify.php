<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;

/**
 * Class Notify
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Notify
{
    // (Used in twig template)
    const VIEW_NONE   = 'none';
    const VIEW_BEFORE = 'before';
    const VIEW_AFTER  = 'after';

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed
     */
    private $source;

    /**
     * @var Recipient
     */
    private $from;

    /**
     * @var ArrayCollection
     */
    private $recipients;

    /**
     * @var ArrayCollection
     */
    private $extraRecipients;

    /**
     * @var ArrayCollection
     */
    private $copies;

    /**
     * @var ArrayCollection
     */
    private $extraCopies;

    /**
     * @var ArrayCollection|InvoiceInterface[]
     */
    private $invoices;

    /**
     * @var ArrayCollection|ShipmentInterface[]
     */
    private $shipments;

    /**
     * @var ArrayCollection|ShipmentLabelInterface[]
     */
    private $labels;

    /**
     * @var ArrayCollection|AttachmentInterface[]
     */
    private $attachments;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $paymentMessage;

    /**
     * @var string
     */
    private $shipmentMessage;

    /**
     * @var string
     */
    private $customMessage;

    /**
     * @var string
     */
    private $includeView;

    /**
     * (For supplier order)
     * @var bool
     */
    private $includeForm;

    /**
     * @var string
     */
    private $report;

    /**
     * @var boolean
     */
    private $test;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = NotificationTypes::MANUAL;

        $this->recipients = new ArrayCollection();
        $this->extraRecipients = new ArrayCollection();
        $this->copies = new ArrayCollection();
        $this->extraCopies = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->attachments = new ArrayCollection();

        $this->includeView = static::VIEW_NONE;
        $this->test = false;
    }

    /**
     * Returns the source.
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Sets the source.
     *
     * @param mixed $source
     *
     * @return Notify
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return Notify
     */
    public function setType($type)
    {
        NotificationTypes::isValidType($type);

        $this->type = $type;

        return $this;
    }

    /**
     * Returns the from.
     *
     * @return Recipient
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Sets the from.
     *
     * @param Recipient $from
     *
     * @return Notify
     */
    public function setFrom(Recipient $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Returns the recipients.
     *
     * @return ArrayCollection|Recipient[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Adds the recipient.
     *
     * @param Recipient $recipient
     *
     * @return Notify
     */
    public function addRecipient(Recipient $recipient)
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients->add($recipient);
        }

        return $this;
    }

    /**
     * Removes the recipient.
     *
     * @param Recipient $recipient
     *
     * @return Notify
     */
    public function removeRecipient(Recipient $recipient)
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
        }

        return $this;
    }

    /**
     * Clears the recipients.
     *
     * @return Notify
     */
    public function clearRecipients()
    {
        $this->recipients = new ArrayCollection();

        return $this;
    }

    /**
     * Returns the extra recipients.
     *
     * @return ArrayCollection|Recipient[]
     */
    public function getExtraRecipients()
    {
        return $this->extraRecipients;
    }

    /**
     * Adds the extra recipient.
     *
     * @param Recipient $recipient
     *
     * @return Notify
     */
    public function addExtraRecipient(Recipient $recipient)
    {
        if (!$this->extraRecipients->contains($recipient)) {
            $this->extraRecipients->add($recipient);
        }

        return $this;
    }

    /**
     * Removes the extra recipient.
     *
     * @param Recipient $recipient
     *
     * @return Notify
     */
    public function removeExtraRecipient(Recipient $recipient)
    {
        if ($this->extraRecipients->contains($recipient)) {
            $this->extraRecipients->removeElement($recipient);
        }

        return $this;
    }

    /**
     * Clears the extra recipients.
     *
     * @return Notify
     */
    public function clearExtraRecipients()
    {
        $this->extraRecipients = new ArrayCollection();

        return $this;
    }

    /**
     * Returns the copies.
     *
     * @return ArrayCollection|Recipient
     */
    public function getCopies()
    {
        return $this->copies;
    }

    /**
     * Adds the copy.
     *
     * @param Recipient $copy
     *
     * @return Notify
     */
    public function addCopy(Recipient $copy)
    {
        if (!$this->copies->contains($copy)) {
            $this->copies->add($copy);
        }

        return $this;
    }

    /**
     * Removes the copy.
     *
     * @param Recipient $copy
     *
     * @return Notify
     */
    public function removeCopy(Recipient $copy)
    {
        if ($this->copies->contains($copy)) {
            $this->copies->removeElement($copy);
        }

        return $this;
    }

    /**
     * Clears the copies.
     *
     * @return Notify
     */
    public function clearCopies()
    {
        $this->copies = new ArrayCollection();

        return $this;
    }

    /**
     * Returns the extra copies.
     *
     * @return ArrayCollection|Recipient
     */
    public function getExtraCopies()
    {
        return $this->extraCopies;
    }

    /**
     * Adds the extra copy.
     *
     * @param Recipient $copy
     *
     * @return Notify
     */
    public function addExtraCopy(Recipient $copy)
    {
        if (!$this->extraCopies->contains($copy)) {
            $this->extraCopies->add($copy);
        }

        return $this;
    }

    /**
     * Removes the extra copy.
     *
     * @param Recipient $copy
     *
     * @return Notify
     */
    public function removeExtraCopy(Recipient $copy)
    {
        if ($this->extraCopies->contains($copy)) {
            $this->extraCopies->removeElement($copy);
        }

        return $this;
    }

    /**
     * Clears the extra copies.
     *
     * @return Notify
     */
    public function clearExtraCopies()
    {
        $this->extraCopies = new ArrayCollection();

        return $this;
    }

    /**
     * Returns the invoices.
     *
     * @return array|InvoiceInterface[]
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * Adds the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return Notify
     */
    public function addInvoice(InvoiceInterface $invoice)
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
        }

        return $this;
    }

    /**
     * Removes the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return Notify
     */
    public function removeInvoice(InvoiceInterface $invoice)
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
        }

        return $this;
    }

    /**
     * Returns the shipments.
     *
     * @return ArrayCollection|ShipmentInterface[]
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * Adds the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return Notify
     */
    public function addShipment(ShipmentInterface $shipment)
    {
        if (!$this->shipments->contains($shipment)) {
            $this->shipments->add($shipment);
        }

        return $this;
    }

    /**
     * Removes the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return Notify
     */
    public function removeShipment(ShipmentInterface $shipment)
    {
        if ($this->shipments->contains($shipment)) {
            $this->shipments->removeElement($shipment);
        }

        return $this;
    }

    /**
     * Returns the labels.
     *
     * @return ArrayCollection|ShipmentLabelInterface[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Adds the label.
     *
     * @param ShipmentLabelInterface $label
     *
     * @return Notify
     */
    public function addLabel(ShipmentLabelInterface $label)
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    /**
     * Removes the label.
     *
     * @param ShipmentLabelInterface $label
     *
     * @return Notify
     */
    public function removeLabel(ShipmentLabelInterface $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    /**
     * Returns the attachments.
     *
     * @return ArrayCollection|AttachmentInterface[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Adds the attachment.
     *
     * @param AttachmentInterface $attachment
     *
     * @return Notify
     */
    public function addAttachment(AttachmentInterface $attachment)
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
        }

        return $this;
    }

    /**
     * Removes the attachment.
     *
     * @param AttachmentInterface $attachment
     *
     * @return Notify
     */
    public function removeAttachment(AttachmentInterface $attachment)
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
        }

        return $this;
    }

    /**
     * Returns the subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the subject.
     *
     * @param string $subject
     *
     * @return Notify
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Returns the payment message.
     *
     * @return string
     */
    public function getPaymentMessage()
    {
        return $this->paymentMessage;
    }

    /**
     * Sets the payment message.
     *
     * @param string $paymentMessage
     *
     * @return Notify
     */
    public function setPaymentMessage($paymentMessage)
    {
        $this->paymentMessage = $paymentMessage;

        return $this;
    }

    /**
     * Returns the shipment message.
     *
     * @return string
     */
    public function getShipmentMessage()
    {
        return $this->shipmentMessage;
    }

    /**
     * Sets the shipment message.
     *
     * @param string $message
     *
     * @return Notify
     */
    public function setShipmentMessage($message = null)
    {
        $this->shipmentMessage = $message;

        return $this;
    }

    /**
     * Returns the custom message.
     *
     * @return string
     */
    public function getCustomMessage()
    {
        return $this->customMessage;
    }

    /**
     * Sets the custom message.
     *
     * @param string $customMessage
     *
     * @return Notify
     */
    public function setCustomMessage($customMessage)
    {
        $this->customMessage = $customMessage;

        return $this;
    }

    /**
     * Returns the include view.
     *
     * @return string
     */
    public function getIncludeView()
    {
        return $this->includeView;
    }

    /**
     * Sets the include view.
     *
     * @param string $include
     *
     * @return Notify
     */
    public function setIncludeView($include)
    {
        $this->includeView = $include;

        return $this;
    }

    /**
     * Returns the includeForm.
     *
     * @return bool
     */
    public function isIncludeForm()
    {
        return $this->includeForm;
    }

    /**
     * Sets the includeForm.
     *
     * @param bool $include
     *
     * @return Notify
     */
    public function setIncludeForm($include)
    {
        $this->includeForm = $include;

        return $this;
    }

    /**
     * Returns the report.
     *
     * @return string
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Sets the report.
     *
     * @param string $report
     *
     * @return Notify
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Returns whether this is a test.
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->test;
    }

    /**
     * Sets whether this is a test.
     *
     * @param bool $test
     *
     * @return Notify
     */
    public function setTest($test)
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Returns whether there is no defined message.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->subject)
            || (empty($this->customMessage) && empty($this->paymentMessage) && empty($this->shipmentMessage));
    }
}
