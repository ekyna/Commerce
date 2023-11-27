<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section;

use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\Writer\WriterInterface;
use Ekyna\Component\Commerce\Report\Writer\XlsWriter;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class CustomersSection
 * @package Ekyna\Component\Commerce\Report\Section
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomersSection implements SectionInterface
{
    final public const NAME = 'customers';

    /** @var array<int, array<string, Margin> */
    private array $data;
    /** @var array<int, array<int, string>> */
    private array $names;
    /** @var array<int, string> */
    private array $years;

    public function initialize(ReportConfig $config): void
    {
        $this->data = [];
        $this->names = [];
        $this->years = $config->range->getYears();
    }

    public function read(ResourceInterface $resource): void
    {
        if (!$resource instanceof OrderInvoiceInterface) {
            throw new UnexpectedTypeException($resource, OrderInvoiceInterface::class);
        }

        if (null === $customer = $resource->getOrder()->getCustomer()) {
            $customer = new class() {
                public function getId(): int
                {
                    return 0;
                }

                public function __toString(): string
                {
                    return 'Unknown';
                }

                public function getCustomerGroup(): object
                {
                    return new class() {
                        public function __toString(): string
                        {
                            return 'Unknown';
                        }
                    };
                }
            };
        }

        $id = $customer->getId();
        $year = $resource->getCreatedAt()->format('Y');

        if (!isset($this->names[$id])) {
            $this->names[$id] = [
                (string)$customer,
                (string)$customer->getCustomerGroup(),
            ];
        }

        if (!isset($this->data[$id][$year])) {
            $this->data[$id][$year] = new Margin();
        }

        $this->data[$id][$year]->merge($resource->getMargin());
    }

    public function write(WriterInterface $writer): void
    {
        if ($writer instanceof XlsWriter) {
            $this->writeXls($writer);

            return;
        }

        throw new UnexpectedValueException('Unsupported writer');
    }

    private function writeXls(XlsWriter $writer): void
    {
        $sheet = $writer->createSheet('Customers'); // TODO trans

        $writer->writeMarginHeaders(['Customer' => 80, 'Group' => 80], $this->years); // TODO trans

        // Values
        $row = 3;
        foreach ($this->names as $id => $name) {
            $row++;

            // Row header
            $sheet->getCell([1, $row])->setValue($name[0]);
            $sheet->getCell([2, $row])->setValue($name[1]);

            // Values
            foreach ($this->years as $yearIndex => $year) {
                $data = $this->data[$id][$year] ?? new Margin();

                $writer->writeMarginCells($data, $yearIndex, $row);
            }
        }
    }

    public function requiresResources(): array
    {
        return [OrderInvoiceInterface::class];
    }

    public function supportsWriter(string $writerClass): bool
    {
        return $writerClass === XlsWriter::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTitle(): TranslatableInterface
    {
        return t('customer.label.plural', [], 'EkynaCommerce');
    }
}
