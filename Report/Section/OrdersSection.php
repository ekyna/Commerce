<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section;

use Ekyna\Component\Commerce\Common\Util\DateUtil;
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

use function Symfony\Component\Translation\t;

/**
 * Class OrdersSection
 * @package Ekyna\Component\Commerce\Report\Section
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrdersSection implements SectionInterface
{
    final public const NAME = 'orders';

    private string $locale;
    /** @var array<string, array<int, OrderData>> */
    private array $data;
    /** @var array<int, string> */
    private array $years;

    public function __construct(
        private readonly OrderUtil $util
    ) {
    }

    public function initialize(ReportConfig $config): void
    {
        $this->locale = $config->locale;
        $this->data = [];
        $this->years = $config->range->getYears();
    }

    public function read(ResourceInterface $resource): void
    {
        if (!$resource instanceof OrderInterface) {
            throw new UnexpectedTypeException($resource, OrderInterface::class);
        }

        $gross = $this->util->getGrossCalculator()->calculateSale($resource);
        $commercial = $this->util->getCommercialCalculator()->calculateSale($resource);

        if ($gross->getSellingPrice()->isZero() && $commercial->getSellingPrice()->isZero()) {
            return;
        }

        [$year, $month] = explode('-', $resource->getAcceptedAt()->format('Y-n'));

        if (!isset($this->data[$year][$month])) {
            $this->data[$year][$month] = $this->util->create();
        }

        $data = $this->data[$year][$month];

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
        $sheet = $writer->createSheet('Orders'); // TODO Trans

        $writer->writeOrderStatHeaders('Month', 23, $this->years); // TODO Trans

        // Values
        $row = 2;
        foreach (DateUtil::getMonths($this->locale) as $monthIndex => $month) {
            $row++;

            // Row header
            $sheet->getCell([1, $row])->setValue($month);

            // Values
            foreach ($this->years as $yearIndex => $year) {
                $col = 2 + $yearIndex * 3;

                $data = $this->data[$year][$monthIndex] ?? $this->util->create();

                $writer->writeOrderStatCells($data, $col, $row);
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
        return t('order.label.plural', [], 'EkynaCommerce');
    }
}
