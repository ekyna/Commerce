<?php

namespace Ekyna\Component\Commerce\Accounting\Export;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class AccountingWriter
 * @package Ekyna\Component\Commerce\Accounting\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingWriter
{
    /**
     * @var resource
     */
    private $handle;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure;

    /***
     * @var string
     */
    private $date;

    /***
     * @var string
     */
    private $identity;

    /**
     * @var string
     */
    private $number;


    /**
     * Constructor.
     *
     * @param string $delimiter
     * @param string $enclosure
     */
    public function __construct(string $delimiter = ';', string $enclosure = '"')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }

    /**
     * Opens the file for writing.
     *
     * @param string $path
     */
    public function open(string $path)
    {
        if (false === $this->handle = fopen($path, "w")) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }
    }

    /**
     * Closes the file.
     */
    public function close()
    {
        if (false === $this->handle) {
            return;
        }

        fclose($this->handle);
    }

    /**
     * Configures the writer for the given subject.
     *
     * @param InvoiceInterface|PaymentInterface $subject
     */
    public function configure($subject)
    {
        if ($subject instanceof InvoiceInterface) {
            $this->date = $subject->getCreatedAt()->format('Y-m-d');
        } elseif ($subject instanceof PaymentInterface) {
            $this->date = $subject->getCompletedAt()->format('Y-m-d');
        } else {
            throw new InvalidArgumentException(
                "Expected instance of " . InvoiceInterface::class . " or " . PaymentInterface::class
            );
        }

        $this->number = $subject->getNumber();

        $sale = $subject->getSale();

        if ($customer = $sale->getCustomer()) {
            $this->identity = $customer->getFirstName() . ' ' . $customer->getLastName();
        } else {
            $this->identity = $sale->getFirstName() . ' ' . $sale->getLastName();
        }
    }

    /**
     * Writes the debit line.
     *
     * @param string    $account
     * @param string    $amount
     * @param \DateTime $date
     */
    public function debit($account, $amount, \DateTime $date)
    {
        $data = [
            $this->date,
            $account,
            $this->identity,
            null,
            $amount,
            $this->number,
            $date->format('Y-m-d'),
        ];

        if (false === fputcsv($this->handle, $data, ';', '"')) {
            throw new RuntimeException("Failed to write line.");
        }
    }

    /**
     * Writes the credit line.
     *
     * @param string $account
     * @param string $amount
     * @param \DateTime $date
     */
    public function credit($account, $amount, \DateTime $date)
    {
        $data = [
            $this->date,
            $account,
            $this->identity,
            $amount,
            null,
            $this->number,
            $date->format('Y-m-d'),
        ];

        if (false === fputcsv($this->handle, $data, ';', '"')) {
            throw new RuntimeException("Failed to write line.");
        }
    }
}
