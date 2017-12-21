<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Stock\Helper\AvailabilityHelperInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;

/**
 * Class AvailabilityViewType
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AvailabilityViewType extends AbstractViewType
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var AvailabilityHelperInterface
     */
    protected $availabilityHelper;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface      $subjectHelper
     * @param AvailabilityHelperInterface $availabilityHelper
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        AvailabilityHelperInterface $availabilityHelper
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->availabilityHelper = $availabilityHelper;
    }

    /**
     * @inheritdoc
     */
    public function buildItemView(Model\SaleItemInterface $item, LineView $view, array $options)
    {
        if ($item->isCompound()) {
            return;
        }

        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return;
        }

        if (!$subject instanceof StockSubjectInterface) {
            return;
        }

        if (0 < $available = $this->availabilityHelper->getAvailableQuantity($subject)) {
            $quantity = $item->getTotalQuantity();
            if ($quantity > $available) {
                $view
                    ->setAvailability($this->formatter->number($available)) // TODO packaging
                    ->addClass('warning');
            }

            return;
        }

        if (!empty($message = $this->availabilityHelper->getAvailabilityMessage($subject))) {
            $view
                ->setAvailability($message)
                ->addClass('warning');
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsSale(Model\SaleInterface $sale)
    {
        if ($sale instanceof CartInterface || $sale instanceof QuoteInterface) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_commerce_availability';
    }
}
