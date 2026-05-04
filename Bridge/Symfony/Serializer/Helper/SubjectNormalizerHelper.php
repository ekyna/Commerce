<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

use function sprintf;

/**
 * Class SubjectNormalizerHelper
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Move to bundle (as there are bundle dependencies)
 */
class SubjectNormalizerHelper
{
    use FormatterAwareTrait;
    use NormalizerAwareTrait;

    public function __construct(
        FormatterFactory                              $formatterFactory,
        protected readonly ConstantsHelper            $constantHelper,
        protected readonly ResourceHelper             $resourceHelper,
        protected readonly RepositoryFactoryInterface $repositoryFactory
    ) {
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * Normalize the subject's stock data.
     */
    public function normalizeStock(StockSubjectInterface $subject, string $format = null, array $context = []): array
    {
        $data = [];

        if (ResourceNormalizer::contextHasGroup(Group::STOCK_VIEW, $context)) {
            $data = $this->normalizeStockView($subject);
        }

        if (ResourceNormalizer::contextHasGroup(Group::STOCK_DATA, $context)) {
            $data = $this->normalizeStockData($subject);
        }

        if (ResourceNormalizer::contextHasGroup(Group::STOCK_UNIT, $context)) {
            $stockUnits = $this->findStockUnits($subject);

            $data['stock_units'] = $this->normalizer->normalize($stockUnits, $format, $context);
        }

        return $data;
    }

    protected function normalizeStockView(StockSubjectInterface $subject): array
    {
        $translator = $this->constantHelper->getTranslator();
        $formatter = $this->getFormatter();

        if (null !== $eda = $subject->getEstimatedDateOfArrival()) {
            $eda = $formatter->date($eda);
        } else {
            $eda = $translator->trans('value.undefined', [], 'EkynaUi');
        }
        if (null !== $releasedAt = $subject->getReleasedAt()) {
            $releasedAt = $formatter->date($releasedAt);
        } else {
            $releasedAt = $translator->trans('value.undefined', [], 'EkynaUi');
        }

        $precision = Units::getPrecision($subject->getUnit());

        $virtual = $subject->getVirtualStock()->toFixed($precision);
        if (null !== $pending = $this->getPendingQuantity($subject)) {
            $virtual = sprintf('%s (+%s)', $virtual, $pending->toFixed($precision));
        }

        return [
            //'mode_label'    => $this->constantHelper->renderStockSubjectModeLabel($subject),
            'mode_badge'     => $this->constantHelper->renderStockSubjectModeBadge($subject),
            //'state_label'   => $this->constantHelper->renderStockSubjectStateLabel($subject),
            'state_badge'    => $this->constantHelper->renderStockSubjectStateBadge($subject),
            'unit'           => $this->constantHelper->renderUnit($subject->getUnit()),
            'in'             => $subject->getInStock()->toFixed($precision),
            'available'      => $subject->getAvailableStock()->toFixed($precision),
            'virtual'        => $virtual,
            'floor'          => $subject->getStockFloor()->toFixed($precision),
            'geocode'        => $subject->getGeocode(),
            'replenishment'  => $subject->getReplenishmentTime(),
            'eda'            => $eda,
            'released_at'    => $releasedAt,
            'hs_code'        => $subject->getHsCode(),
            'moq'            => $subject->getMinimumOrderQuantity()->toFixed($precision),
            'weight'         => $subject->getWeight()->toFixed(3),
            'width'          => $subject->getWidth(),
            'height'         => $subject->getHeight(),
            'depth'          => $subject->getDepth(),
            'package_weight' => $subject->getPackageWeight()->toFixed(3),
            'package_width'  => $subject->getPackageWidth(),
            'package_height' => $subject->getPackageHeight(),
            'package_depth'  => $subject->getPackageDepth(),
            'physical'       => $this->badge($subject->isPhysical(), 'success', 'warning'),
            'quote_only'     => $this->badge($subject->isQuoteOnly()),
            'end_of_life'    => $this->badge($subject->isEndOfLife()),
        ];
    }

    protected function normalizeStockData(StockSubjectInterface $subject): array
    {
        $precision = Units::getPrecision($subject->getUnit());

        return [
            'mode'           => $subject->getStockMode(),
            'state'          => $subject->getStockState(),
            'unit'           => $subject->getUnit(),
            'in'             => $subject->getInStock()->toFixed($precision),
            'available'      => $subject->getAvailableStock()->toFixed($precision),
            'virtual'        => $subject->getVirtualStock()->toFixed($precision),
            'floor'          => $subject->getStockFloor()->toFixed($precision),
            'geocode'        => $subject->getGeocode(),
            'replenishment'  => $subject->getReplenishmentTime(),
            'eda'            => $subject->getEstimatedDateOfArrival()?->format('Y-m-d'),
            'released_at'    => $subject->getReleasedAt()?->format('Y-m-d'),
            'hs_code'        => $subject->getHsCode(),
            'moq'            => $subject->getMinimumOrderQuantity()->toFixed($precision),
            'weight'         => $subject->getWeight(),
            'width'          => $subject->getWidth(),
            'height'         => $subject->getHeight(),
            'depth'          => $subject->getDepth(),
            'package_weight' => $subject->getPackageWeight()->toFixed(3),
            'package_width'  => $subject->getPackageWidth(),
            'package_height' => $subject->getPackageHeight(),
            'package_depth'  => $subject->getPackageDepth(),
            'physical'       => $subject->isPhysical(),
            'quote_only'     => $subject->isQuoteOnly(),
            'end_of_life'    => $subject->isEndOfLife(),
        ];
    }

    private function badge(bool $flag, string $true = 'warning', string $false = 'success'): string
    {
        $translator = $this->constantHelper->getTranslator();

        $label = $flag
            ? $translator->trans('value.yes', [], 'EkynaUi')
            : $translator->trans('value.no', [], 'EkynaUi');

        $theme = $flag ? $true : $false;

        return sprintf('<span class="label label-%s">%s</span>', $theme, $label);
    }

    private function getPendingQuantity(StockSubjectInterface $subject): ?Decimal
    {
        $repository = $this->repositoryFactory->getRepository('ekyna_commerce.supplier_order_item');

        if (!$repository instanceof SupplierOrderItemRepositoryInterface) {
            throw new UnexpectedTypeException($repository, SupplierOrderItemRepositoryInterface::class);
        }

        return $repository->getPendingQuantity($subject);
    }

    /**
     * Sorts the stock units.
     *
     * @param StockSubjectInterface $subject
     *
     * @return StockUnitInterface[]
     */
    private function findStockUnits(StockSubjectInterface $subject): array
    {
        // TODO use \Ekyna\Component\Commerce\Stock\Helper\StockUnitHelper::getRepository
        $repository = $this->repositoryFactory->getRepository($subject::getStockUnitClass());

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new UnexpectedTypeException($repository, StockUnitRepositoryInterface::class);
        }

        $stockUnits = array_merge(
            $repository->findNotClosedBySubject($subject),
            $repository->findLatestClosedBySubject($subject, 0)
        );

        // Sort by "created/closed at" date desc
        usort($stockUnits, function (StockUnitInterface $a, StockUnitInterface $b) {
            if ($a->getState() === StockUnitStates::STATE_CLOSED && $b->getState() !== StockUnitStates::STATE_CLOSED) {
                return 1;
            }

            if ($a->getState() !== StockUnitStates::STATE_CLOSED && $b->getState() === StockUnitStates::STATE_CLOSED) {
                return -1;
            }

            if ($a->getState() === StockUnitStates::STATE_CLOSED && $b->getState() === StockUnitStates::STATE_CLOSED) {
                $aDate = $a->getClosedAt()->getTimestamp();
                $bDate = $b->getClosedAt()->getTimestamp();

                if ($aDate > $bDate) {
                    return -1;
                }

                if ($aDate < $bDate) {
                    return 1;
                }
            }

            $aDate = $a->getCreatedAt()->getTimestamp();
            $bDate = $b->getCreatedAt()->getTimestamp();

            if ($aDate > $bDate) {
                return -1;
            }

            if ($aDate < $bDate) {
                return 1;
            }

            return 0;
        });

        return $stockUnits;
    }
}
