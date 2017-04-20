<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Class SubjectNormalizerHelper
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectNormalizerHelper
{
    use FormatterAwareTrait;
    use NormalizerAwareTrait;

    protected ConstantsHelper $constantHelper;
    protected ResourceHelper $resourceHelper;
    protected RepositoryFactoryInterface $repositoryFactory;

    public function __construct(
        FormatterFactory $formatterFactory,
        ConstantsHelper $constantHelper,
        ResourceHelper $resourceHelper,
        RepositoryFactoryInterface $repositoryFactory
    ) {
        $this->formatterFactory = $formatterFactory;
        $this->constantHelper = $constantHelper;
        $this->resourceHelper = $resourceHelper;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * Normalize the subject's stock data.
     */
    public function normalizeStock(StockSubjectInterface $subject, string $format = null, array $context = []): array
    {
        $translator = $this->constantHelper->getTranslator();
        $formatter = $this->getFormatter();

        if (null !== $eda = $subject->getEstimatedDateOfArrival()) {
            $eda = $formatter->date($eda);
        } else {
            $eda = $translator->trans('value.undefined', [], 'EkynaUi');
        }

        $stockUnits = $this->findStockUnits($subject);

        return [
            'mode_label'    => $this->constantHelper->renderStockSubjectModeLabel($subject),
            'mode_badge'    => $this->constantHelper->renderStockSubjectModeBadge($subject),
            'state_label'   => $this->constantHelper->renderStockSubjectStateLabel($subject),
            'state_badge'   => $this->constantHelper->renderStockSubjectStateBadge($subject),
            'in'            => $formatter->number($subject->getInStock()),
            'available'     => $formatter->number($subject->getAvailableStock()),
            'virtual'       => $formatter->number($subject->getVirtualStock()),
            'floor'         => $formatter->number($subject->getStockFloor()),
            'geocode'       => $subject->getGeocode(),
            'replenishment' => $formatter->number($subject->getReplenishmentTime()),
            'eda'           => $eda,
            'moq'           => $formatter->number($subject->getMinimumOrderQuantity()),
            'quote_only'    => $subject->isQuoteOnly()
                ? $translator->trans('value.yes', [], 'EkynaUi')
                : $translator->trans('value.no', [], 'EkynaUi'),
            'end_of_life'   => $subject->isEndOfLife()
                ? $translator->trans('value.yes', [], 'EkynaUi')
                : $translator->trans('value.no', [], 'EkynaUi'),
            'stock_units'   => $this->normalizer->normalize($stockUnits, $format, $context),
        ];
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
