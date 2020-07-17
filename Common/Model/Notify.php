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
     * @var ArrayCollection|Recipient[]
     */
    private $recipients;

    /**
     * @var ArrayCollection|Recipient[]
     */
    private $extraRecipients;

    /**
     * @var ArrayCollection|Recipient[]
     */
    private $copies;

    /**
     * @var ArrayCollection|Recipient[]
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
    private $buttonLabel;

    /**
     * @var string
     */
    private $buttonUrl;

    /**
     * @var bool
     */
    private $unsafe;

    /**
     * @var bool
     */
    private $error;

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
        $this->includeForm = false;
        $this->unsafe = false;
        $this->error = false;
        $this->report = '';
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
    public function getType(): string
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
    public function setType(string $type): self
    {
        NotificationTypes::isValid($type);

        $this->type = $type;

        return $this;
    }

    /**
     * Returns the from.
     *
     * @return Recipient
     */
    public function getFrom(): ?Recipient
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
    public function setFrom(Recipient $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Returns the recipients.
     *
     * @return ArrayCollection|Recipient[]
     */
    public function getRecipients(): ArrayCollection
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
    public function addRecipient(Recipient $recipient): self
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
    public function removeRecipient(Recipient $recipient): self
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
    public function clearRecipients(): self
    {
        $this->recipients = new ArrayCollection();

        return $this;
    }

    /**
     * Returns the extra recipients.
     *
     * @return ArrayCollection|Recipient[]
     */
    public function getExtraRecipients(): ArrayCollection
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
    public function addExtraRecipient(Recipient $recipient): self
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
    public function removeExtraRecipient(Recipient $recipient): self
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
    public function clearExtraRecipients(): self
    {
        $this->extraRecipients = new ArrayCollection();

        return $this;
    }

    /**
     * Returns the copies.
     *
     * @return ArrayCollection|Recipient[]
     */
    public function getCopies(): ArrayCollection
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
    public function addCopy(Recipient $copy): self
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
    public function removeCopy(Recipient $copy): self
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
    public function clearCopies(): self
    {
        $this->copies = new ArrayCollection();

        return $this;
    }

    /**
     * Returns the extra copies.
     *
     * @return ArrayCollection|Recipient[]
     */
    public function getExtraCopies(): ArrayCollection
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
    public function addExtraCopy(Recipient $copy): self
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
    public function removeExtraCopy(Recipient $copy): self
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
    public function clearExtraCopies(): self
    {
        $this->extraCopies = new ArrayCollection();

        return $this;
    }

    /**
     * Returns the invoices.
     *
     * @return ArrayCollection|InvoiceInterface[]
     */
    public function getInvoices(): ArrayCollection
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
    public function addInvoice(InvoiceInterface $invoice): self
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
    public function removeInvoice(InvoiceInterface $invoice): self
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
    public function getShipments(): ArrayCollection
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
    public function addShipment(ShipmentInterface $shipment): self
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
    public function removeShipment(ShipmentInterface $shipment): self
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
    public function getLabels(): ArrayCollection
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
    public function addLabel(ShipmentLabelInterface $label): self
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
    public function removeLabel(ShipmentLabelInterface $label): self
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
    public function getAttachments(): ArrayCollection
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
    public function addAttachment(AttachmentInterface $attachment): self
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
    public function removeAttachment(AttachmentInterface $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
        }

        return $this;
    }

    /**
     * Returns the subject.
     *
     * @return string|null
     */
    public function getSubject(): ?string
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
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Returns the payment message.
     *
     * @return string|null
     */
    public function getPaymentMessage(): ?string
    {
        return $this->paymentMessage;
    }

    /**
     * Sets the payment message.
     *
     * @param string $message
     *
     * @return Notify
     */
    public function setPaymentMessage(string $message = null): self
    {
        $this->paymentMessage = $message;

        return $this;
    }

    /**
     * Returns the shipment message.
     *
     * @return string
     */
    public function getShipmentMessage(): ?string
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
    public function setShipmentMessage(string $message = null): self
    {
        $this->shipmentMessage = $message;

        return $this;
    }

    /**
     * Returns the custom message.
     *
     * @return string
     */
    public function getCustomMessage(): ?string
    {
        return $this->customMessage;
    }

    /**
     * Sets the custom message.
     *
     * @param string $message
     *
     * @return Notify
     */
    public function setCustomMessage(string $message = null): self
    {
        $this->customMessage = $message;

        return $this;
    }

    /**
     * Returns the included view.
     *
     * @return string
     */
    public function getIncludeView(): string
    {
        return $this->includeView;
    }

    /**
     * Sets the included view.
     *
     * @param string $include
     *
     * @return Notify
     */
    public function setIncludeView(string $include): self
    {
        $this->includeView = $include;

        return $this;
    }

    /**
     * Returns whether to include form.
     *
     * @return bool
     */
    public function isIncludeForm(): bool
    {
        return $this->includeForm;
    }

    /**
     * Sets whether to include form.
     *
     * @param bool $include
     *
     * @return Notify
     */
    public function setIncludeForm(bool $include): self
    {
        $this->includeForm = $include;

        return $this;
    }

    /**
     * Returns the button label.
     *
     * @return string
     */
    public function getButtonLabel(): ?string
    {
        return $this->buttonLabel;
    }

    /**
     * Sets the button label.
     *
     * @param string $label
     *
     * @return Notify
     */
    public function setButtonLabel(string $label = null): Notify
    {
        $this->buttonLabel = $label;

        return $this;
    }

    /**
     * Returns the button url.
     *
     * @return string
     */
    public function getButtonUrl(): ?string
    {
        return $this->buttonUrl;
    }

    /**
     * Sets the button url.
     *
     * @param string $url
     *
     * @return Notify
     */
    public function setButtonUrl(string $url = null): Notify
    {
        $this->buttonUrl = $url;

        return $this;
    }

    /**
     * Returns the unsafe.
     *
     * @return bool
     */
    public function isUnsafe(): bool
    {
        return $this->unsafe;
    }

    /**
     * Sets the unsafe.
     *
     * @param bool $unsafe
     *
     * @return Notify
     */
    public function setUnsafe(bool $unsafe): Notify
    {
        $this->unsafe = $unsafe;

        return $this;
    }

    /**
     * Returns the error.
     *
     * @return bool
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * Sets the error.
     *
     * @param bool $error
     *
     * @return Notify
     */
    public function setError(bool $error): Notify
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Returns the report.
     *
     * @return string
     */
    public function getReport(): string
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
    public function setReport(string $report): self
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Returns whether this is a test.
     *
     * @return bool
     */
    public function isTest(): bool
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
    public function setTest(bool $test): self
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Returns whether there is no defined message.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->subject) || (
            empty($this->customMessage) &&
            empty($this->paymentMessage) &&
            empty($this->shipmentMessage)
        );
    }
}
