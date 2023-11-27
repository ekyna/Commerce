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
 * Class InvoicesSection
 * @package Ekyna\Component\Commerce\Report\Section
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupsSection implements SectionInterface
{
    final public const NAME = 'customer_groups';

    /** @var array<string, array<int, Margin>> */
    private array $data;
    /** @var array<int, string> */
    private array $names;
    /** @var array<int, string> */
    private array $years;

    /**
     * @inheritDoc
     */
    public function initialize(ReportConfig $config): void
    {
        $this->data = [];
        $this->names = [];
        $this->years = $config->range->getYears();
    }

    /**
     * @inheritDoc
     */
    public function read(ResourceInterface $resource): void
    {
        if (!$resource instanceof OrderInvoiceInterface) {
            throw new UnexpectedTypeException($resource, OrderInvoiceInterface::class);
        }

        if (null === $group = $resource->getOrder()->getCustomerGroup()) {
            $group = new class(0, 'Unknown') {
                public function __construct(
                    private readonly int    $id,
                    private readonly string $name,
                ) {
                }

                public function getId(): int
                {
                    return $this->id;
                }

                public function getName(): string
                {
                    return $this->name;
                }
            };
        }

        $id = $group->getId();
        $year = $resource->getCreatedAt()->format('Y');

        if (!isset($this->names[$id])) {
            $this->names[$id] = $group->getName();
        }

        if (!isset($this->data[$id][$year])) {
            $this->data[$id][$year] = new Margin();
        }

        $this->data[$id][$year]->merge($resource->getMargin());
    }

    /**
     * @inheritDoc
     */
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
        $sheet = $writer->createSheet('Customer groups'); // TODO trans

        $writer->writeMarginHeaders(['Group' => 80], $this->years); // TODO trans

        // Values
        $row = 3;
        foreach ($this->names as $id => $name) {
            $row++;

            // Row header
            $sheet->getCell([1, $row])->setValue($name);

            // Values
            foreach ($this->years as $yearIndex => $year) {
                $data = $this->data[$id][$year] ?? new Margin();

                $writer->writeMarginCells($data, $yearIndex, $row);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function requiresResources(): array
    {
        return [OrderInvoiceInterface::class];
    }

    /**
     * @inheritDoc
     */
    public function supportsWriter(string $writerClass): bool
    {
        return $writerClass === XlsWriter::class;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): TranslatableInterface
    {
        return t('customer_group.label.plural', [], 'EkynaCommerce');
    }
}
