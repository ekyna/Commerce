<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Class SubjectNormalizerHelper
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectNormalizerHelper
{
    use NormalizerAwareTrait;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var ConstantsHelper
     */
    protected $constantHelper;

    /**
     * @var ResourceHelper
     */
    protected $resourceHelper;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;


    /**
     * Constructor.
     *
     * @param Formatter              $formatter
     * @param ConstantsHelper        $constantHelper
     * @param ResourceHelper         $resourceHelper
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        Formatter $formatter,
        ConstantsHelper $constantHelper,
        ResourceHelper $resourceHelper,
        EntityManagerInterface $entityManager
    ) {
        $this->formatter = $formatter;
        $this->constantHelper = $constantHelper;
        $this->resourceHelper = $resourceHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * Normalize the subject's stock data.
     *
     * @param StockSubjectInterface $subject
     * @param string                $format
     * @param array                 $context
     *
     * @return array
     */
    public function normalizeStock(StockSubjectInterface $subject, $format = null, array $context = [])
    {
        $translator = $this->constantHelper->getTranslator();

        if (null !== $eda = $subject->getEstimatedDateOfArrival()) {
            $eda = $this->formatter->date($eda);
        } else {
            $eda = $translator->trans('ekyna_core.value.undefined');
        }

        $stockUnits = $this->findStockUnits($subject);

        return [
            'mode_label'    => $this->constantHelper->renderStockSubjectModeLabel($subject),
            'mode_badge'    => $this->constantHelper->renderStockSubjectModeBadge($subject),
            'state_label'   => $this->constantHelper->renderStockSubjectStateLabel($subject),
            'state_badge'   => $this->constantHelper->renderStockSubjectStateBadge($subject),
            'in'            => $this->formatter->number($subject->getInStock()),
            'available'     => $this->formatter->number($subject->getAvailableStock()),
            'virtual'       => $this->formatter->number($subject->getVirtualStock()),
            'floor'         => $this->formatter->number($subject->getStockFloor()),
            'geocode'       => $subject->getGeocode(),
            'replenishment' => $this->formatter->number($subject->getReplenishmentTime()),
            'eda'           => $eda,
            'moq'           => $this->formatter->number($subject->getMinimumOrderQuantity()),
            'quote_only'    => $subject->isQuoteOnly()
                ? $translator->trans('ekyna_core.value.yes')
                : $translator->trans('ekyna_core.value.no'),
            'end_of_life'   => $subject->isEndOfLife()
                ? $translator->trans('ekyna_core.value.yes')
                : $translator->trans('ekyna_core.value.no'),
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
    private function findStockUnits(StockSubjectInterface $subject)
    {
        /** @var StockUnitRepositoryInterface $repository */
        $repository = $this->entityManager->getRepository($subject::getStockUnitClass());

        /** @var StockUnitInterface[] $stockUnits */
        $stockUnits = array_merge(
            $repository->findNotClosedBySubject($subject),
            $repository->findLatestClosedBySubject($subject)
        );

        // Sort by "created/closed at" date desc
        usort($stockUnits, function(StockUnitInterface $a, StockUnitInterface $b) {
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
