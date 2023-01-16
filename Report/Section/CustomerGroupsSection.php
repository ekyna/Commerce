<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\Section\Model\OrderData;
use Ekyna\Component\Commerce\Report\Util\OrderUtil;
use Ekyna\Component\Commerce\Report\Writer\WriterInterface;
use Ekyna\Component\Commerce\Report\Writer\XlsWriter;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

use function array_walk;
use function Symfony\Component\Translation\t;
use function uasort;

/**
 * Class CustomerGroupsSection
 * @package Ekyna\Component\Commerce\Report\Section
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupsSection implements SectionInterface
{
    final public const NAME = 'customer_groups';

    /** @var array<int, array<string, OrderData> */
    private array $data;
    /** @var array<int, string> */
    private array $names;
    /** @var array<int, string> */
    private array $years;

    public function __construct(
        private readonly OrderUtil $util
    ) {
    }

    public function initialize(ReportConfig $config): void
    {
        $this->data = [];
        $this->names = [];
        $this->years = $config->range->getYears();
    }

    public function read(ResourceInterface $resource): void
    {
        if (!$resource instanceof OrderInterface) {
            throw new UnexpectedTypeException($resource, OrderInterface::class);
        }

        if (null === $group = $resource->getCustomerGroup()) {
            $group = new class(0, 'Unknown') {
                public function __construct(
                    private readonly int $id,
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

        $gross = $this->util->getGrossCalculator()->calculateSale($resource);
        $commercial = $this->util->getCommercialCalculator()->calculateSale($resource);

        if ($gross->getSellingPrice()->isZero() && $commercial->getSellingPrice()->isZero()) {
            return;
        }

        $id = $group->getId();
        $year = $resource->getAcceptedAt()->format('Y');

        if (!isset($this->names[$id])) {
            $this->names[$id] = $group->getName();
        }

        if (!isset($this->data[$id][$year])) {
            $this->data[$id][$year] = $this->util->create();
        }

        $data = $this->data[$id][$year];

        $data->grossMargin->merge($gross);
        $data->commercialMargin->merge($commercial);
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
        $sheet = $writer->createSheet('Customer groups'); // TODO Trans

        $writer->writeOrderStatHeaders('Customer groups', 46, $this->years); // TODO Trans

        // Calculate total
        array_walk($this->data, function (array &$data) {
            $total = new Decimal(0);
            /** @var OrderData $datum */
            foreach ($data as $datum) {
                $total += $datum->grossMargin->getRevenue();
            }
            $data['total'] = $total;
        });

        // Sort by highest revenue
        uasort($this->data, function (array $a, array $b): int {
            return $b['total'] <=> $a['total'];
        });

        // Values
        $row = 2;
        foreach ($this->data as $id => $years) {
            $row++;

            // Row header
            $sheet->getCell([1, $row])->setValue($this->names[$id]);

            foreach ($this->years as $index => $year) {
                $col = 2 + $index * 3;

                $datum = $years[$year] ?? $this->util->create();

                $writer->writeOrderStatCells($datum, $col, $row);
            }
        }
    }

    public function requiresResources(): array
    {
        return [OrderInterface::class];
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
        return t('customer_group.label.plural', [], 'EkynaCommerce');
    }
}
