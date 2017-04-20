<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Notify
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Notify
{
    // (Used in templates)
    public const VIEW_NONE   = 'none';
    public const VIEW_BEFORE = 'before';
    public const VIEW_AFTER  = 'after';

    private string             $type   = NotificationTypes::MANUAL;
    private ?ResourceInterface $source = null;
    private ?Recipient         $from   = null;

    /** @var ArrayCollection|Recipient[] */
    private ArrayCollection $recipients;
    /** @var ArrayCollection|Recipient[] */
    private ArrayCollection $extraRecipients;
    /** @var ArrayCollection|Recipient[] */
    private ArrayCollection $copies;
    /** @var ArrayCollection|Recipient[] */
    private ArrayCollection $extraCopies;
    /** @var ArrayCollection|InvoiceInterface[] */
    private ArrayCollection $invoices;
    /** @var ArrayCollection|ShipmentInterface[] */
    private ArrayCollection $shipments;
    /** @var ArrayCollection|ShipmentLabelInterface[] */
    private ArrayCollection $labels;
    /** @var ArrayCollection|AttachmentInterface[] */
    private ArrayCollection $attachments;

    private ?string $subject         = null;
    private ?string $paymentMessage  = null;
    private ?string $shipmentMessage = null;
    private ?string $customMessage   = null;
    private string  $includeView     = Notify::VIEW_NONE;
    private bool    $includeForm     = false; // (For supplier order)
    private ?string $buttonLabel     = null;
    private ?string $buttonUrl       = null;
    private bool    $unsafe          = false;
    private bool    $error           = false;
    private string  $report          = '';
    private bool    $test            = false;


    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->extraRecipients = new ArrayCollection();
        $this->copies = new ArrayCollection();
        $this->extraCopies = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    public function getSource(): ?ResourceInterface
    {
        return $this->source;
    }

    public function setSource(ResourceInterface $source): Notify
    {
        $this->source = $source;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Notify
    {
        NotificationTypes::isValid($type);

        $this->type = $type;

        return $this;
    }

    public function getFrom(): ?Recipient
    {
        return $this->from;
    }

    public function setFrom(Recipient $from): Notify
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return ArrayCollection|Recipient[]
     */
    public function getRecipients(): ArrayCollection
    {
        return $this->recipients;
    }

    public function addRecipient(Recipient $recipient): Notify
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients->add($recipient);
        }

        return $this;
    }

    public function removeRecipient(Recipient $recipient): Notify
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
        }

        return $this;
    }

    public function clearRecipients(): Notify
    {
        $this->recipients = new ArrayCollection();

        return $this;
    }

    /**
     * @return ArrayCollection|Recipient[]
     */
    public function getExtraRecipients(): ArrayCollection
    {
        return $this->extraRecipients;
    }

    public function addExtraRecipient(Recipient $recipient): Notify
    {
        if (!$this->extraRecipients->contains($recipient)) {
            $this->extraRecipients->add($recipient);
        }

        return $this;
    }

    public function removeExtraRecipient(Recipient $recipient): Notify
    {
        if ($this->extraRecipients->contains($recipient)) {
            $this->extraRecipients->removeElement($recipient);
        }

        return $this;
    }

    public function clearExtraRecipients(): Notify
    {
        $this->extraRecipients = new ArrayCollection();

        return $this;
    }

    /**
     * @return ArrayCollection|Recipient[]
     */
    public function getCopies(): ArrayCollection
    {
        return $this->copies;
    }

    public function addCopy(Recipient $copy): Notify
    {
        if (!$this->copies->contains($copy)) {
            $this->copies->add($copy);
        }

        return $this;
    }

    public function removeCopy(Recipient $copy): Notify
    {
        if ($this->copies->contains($copy)) {
            $this->copies->removeElement($copy);
        }

        return $this;
    }

    public function clearCopies(): Notify
    {
        $this->copies = new ArrayCollection();

        return $this;
    }

    /**
     * @return ArrayCollection|Recipient[]
     */
    public function getExtraCopies(): ArrayCollection
    {
        return $this->extraCopies;
    }

    public function addExtraCopy(Recipient $copy): Notify
    {
        if (!$this->extraCopies->contains($copy)) {
            $this->extraCopies->add($copy);
        }

        return $this;
    }

    public function removeExtraCopy(Recipient $copy): Notify
    {
        if ($this->extraCopies->contains($copy)) {
            $this->extraCopies->removeElement($copy);
        }

        return $this;
    }

    public function clearExtraCopies(): Notify
    {
        $this->extraCopies = new ArrayCollection();

        return $this;
    }

    /**
     * @return ArrayCollection|InvoiceInterface[]
     */
    public function getInvoices(): ArrayCollection
    {
        return $this->invoices;
    }

    public function addInvoice(InvoiceInterface $invoice): Notify
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
        }

        return $this;
    }

    public function removeInvoice(InvoiceInterface $invoice): Notify
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|ShipmentInterface[]
     */
    public function getShipments(): ArrayCollection
    {
        return $this->shipments;
    }

    public function addShipment(ShipmentInterface $shipment): Notify
    {
        if (!$this->shipments->contains($shipment)) {
            $this->shipments->add($shipment);
        }

        return $this;
    }

    public function removeShipment(ShipmentInterface $shipment): Notify
    {
        if ($this->shipments->contains($shipment)) {
            $this->shipments->removeElement($shipment);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|ShipmentLabelInterface[]
     */
    public function getLabels(): ArrayCollection
    {
        return $this->labels;
    }

    public function addLabel(ShipmentLabelInterface $label): Notify
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    public function removeLabel(ShipmentLabelInterface $label): Notify
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|AttachmentInterface[]
     */
    public function getAttachments(): ArrayCollection
    {
        return $this->attachments;
    }

    public function addAttachment(AttachmentInterface $attachment): Notify
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
        }

        return $this;
    }

    public function removeAttachment(AttachmentInterface $attachment): Notify
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
        }

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): Notify
    {
        $this->subject = $subject;

        return $this;
    }

    public function getPaymentMessage(): ?string
    {
        return $this->paymentMessage;
    }

    public function setPaymentMessage(?string $message): Notify
    {
        $this->paymentMessage = $message;

        return $this;
    }

    public function getShipmentMessage(): ?string
    {
        return $this->shipmentMessage;
    }

    public function setShipmentMessage(?string $message): Notify
    {
        $this->shipmentMessage = $message;

        return $this;
    }

    public function getCustomMessage(): ?string
    {
        return $this->customMessage;
    }

    public function setCustomMessage(?string $message): Notify
    {
        $this->customMessage = $message;

        return $this;
    }

    public function getIncludeView(): string
    {
        return $this->includeView;
    }

    public function setIncludeView(string $include): Notify
    {
        $this->includeView = $include;

        return $this;
    }

    public function isIncludeForm(): bool
    {
        return $this->includeForm;
    }

    public function setIncludeForm(bool $include): Notify
    {
        $this->includeForm = $include;

        return $this;
    }

    public function getButtonLabel(): ?string
    {
        return $this->buttonLabel;
    }

    public function setButtonLabel(?string $label): Notify
    {
        $this->buttonLabel = $label;

        return $this;
    }

    public function getButtonUrl(): ?string
    {
        return $this->buttonUrl;
    }

    public function setButtonUrl(?string $url): Notify
    {
        $this->buttonUrl = $url;

        return $this;
    }

    public function isUnsafe(): bool
    {
        return $this->unsafe;
    }

    public function setUnsafe(bool $unsafe): Notify
    {
        $this->unsafe = $unsafe;

        return $this;
    }

    public function isError(): bool
    {
        return $this->error;
    }

    public function setError(bool $error): Notify
    {
        $this->error = $error;

        return $this;
    }

    public function getReport(): string
    {
        return $this->report;
    }

    public function setReport(string $report): Notify
    {
        $this->report = $report;

        return $this;
    }

    public function isTest(): bool
    {
        return $this->test;
    }

    public function setTest(bool $test): Notify
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Returns whether there is no defined message.
     */
    public function isEmpty(): bool
    {
        return empty($this->subject)
            || (
                empty($this->customMessage)
                && empty($this->paymentMessage)
                && empty($this->shipmentMessage)
            );
    }
}
