<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Accounting\Export;

use DateTimeInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class AccountingWriter
 * @package Ekyna\Component\Commerce\Accounting\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingWriter
{
    /** @var bool|resource */
    private        $handle;
    private string $separator;
    private string $enclosure;

    private string $date;
    private string $identity;
    private string $number;

    public function __construct(string $delimiter = ',', string $enclosure = '"')
    {
        $this->separator = $delimiter;
        $this->enclosure = $enclosure;
    }

    /**
     * Opens the file for writing.
     */
    public function open(string $path): void
    {
        if (false === $this->handle = fopen($path, 'w')) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }
    }

    /**
     * Closes the file.
     */
    public function close(): void
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
    public function configure($subject): void
    {
        if ($subject instanceof InvoiceInterface) {
            $this->date = $subject->getCreatedAt()->format('Y-m-d');
        } elseif ($subject instanceof PaymentInterface) {
            $this->date = $subject->getCompletedAt()->format('Y-m-d');
        } else {
            throw new UnexpectedTypeException($subject, [InvoiceInterface::class, PaymentInterface::class]);
        }

        $this->number = $subject->getNumber();

        $sale = $subject->getSale();

        if (!empty($company = $sale->getCompany())) {
            $this->identity = $company;
        } elseif ($customer = $sale->getCustomer()) {
            $this->identity = $customer->getFirstName() . ' ' . $customer->getLastName();
        } else {
            $this->identity = $sale->getFirstName() . ' ' . $sale->getLastName();
        }
    }

    /**
     * Writes the debit line.
     */
    public function debit(string $account, string $amount, DateTimeInterface $date): void
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

        if (false === fputcsv($this->handle, $data, $this->separator, $this->enclosure)) {
            throw new RuntimeException('Failed to write line.');
        }
    }

    /**
     * Writes the credit line.
     */
    public function credit(string $account, string $amount, DateTimeInterface $date)
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

        if (false === fputcsv($this->handle, $data, $this->separator, $this->enclosure)) {
            throw new RuntimeException('Failed to write line.');
        }
    }
}
