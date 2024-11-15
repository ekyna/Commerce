<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
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

            $virtual = $formatter->number($subject->getVirtualStock());
            if (null !== $pending = $this->getPendingQuantity($subject)) {
                $virtual = sprintf('%s (+%s)', $virtual, $formatter->number($pending));
            }

            $data = [
                //'mode_label'    => $this->constantHelper->renderStockSubjectModeLabel($subject),
                'mode_badge'     => $this->constantHelper->renderStockSubjectModeBadge($subject),
                //'state_label'   => $this->constantHelper->renderStockSubjectStateLabel($subject),
                'state_badge'    => $this->constantHelper->renderStockSubjectStateBadge($subject),
                'unit'           => $this->constantHelper->renderUnit($subject->getUnit()),
                'in'             => $formatter->number($subject->getInStock()),
                'available'      => $formatter->number($subject->getAvailableStock()),
                'virtual'        => $virtual,
                'floor'          => $formatter->number($subject->getStockFloor()),
                'geocode'        => $subject->getGeocode(),
                'replenishment'  => $formatter->number($subject->getReplenishmentTime()),
                'eda'            => $eda,
                'released_at'    => $releasedAt,
                'hs_code'        => $subject->getHsCode(),
                'moq'            => $formatter->number($subject->getMinimumOrderQuantity()),
                'weight'         => $formatter->number($subject->getWeight()),
                'width'          => $formatter->number($subject->getWidth()),
                'height'         => $formatter->number($subject->getHeight()),
                'depth'          => $formatter->number($subject->getDepth()),
                'package_weight' => $formatter->number($subject->getPackageWeight()),
                'package_width'  => $formatter->number($subject->getPackageWidth()),
                'package_height' => $formatter->number($subject->getPackageHeight()),
                'package_depth'  => $formatter->number($subject->getPackageDepth()),
                'physical'       => $this->badge($subject->isPhysical(), 'success', 'warning'),
                'quote_only'     => $this->badge($subject->isQuoteOnly()),
                'end_of_life'    => $this->badge($subject->isEndOfLife()),
            ];
        }

        if (ResourceNormalizer::contextHasGroup(Group::STOCK_UNIT, $context)) {
            $stockUnits = $this->findStockUnits($subject);

            $data['stock_units'] = $this->normalizer->normalize($stockUnits, $format, $context);
        }

        return $data;
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
            $repository->findLatestClosedBySubject($subject)
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
