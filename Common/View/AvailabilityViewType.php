<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Stock\Helper\AvailabilityHelperInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

use function implode;
use function max;
use function sprintf;

/**
 * Class AvailabilityViewType
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AvailabilityViewType extends AbstractViewType
{
    public function __construct(
        private readonly SubjectHelperInterface      $subjectHelper,
        private readonly AvailabilityHelperInterface $availabilityHelper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function buildItemView(Model\SaleItemInterface $item, LineView $view, array $options): void
    {
        // Not for compound items with only public children
        if ($item->isCompound() && !$item->hasPrivateChildren()) {
            // TODO Resolve by merging children availabilities ?
            return;
        }

        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return;
        }

        if (!$subject instanceof StockSubjectInterface) {
            return;
        }

        $quantity = $item->getTotalQuantity();

        $availability = $this
            ->availabilityHelper
            ->getAvailability($subject, is_null($item->getParent()), $options['private']);

        $messages = $availability->getMessagesForQuantity($quantity);

        $view->availability = sprintf(
            '<span class="availability-%d">%s</span>',
            max(count($messages), 1),
            implode('<br>', $messages)
        );

        if ($quantity > $availability->getMaximumQuantity()) {
            $view->addClass('danger');
        } elseif (!$availability->isAvailableForQuantity($quantity)) {
            $view->addClass('warning');
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsSale(Model\SaleInterface $sale): bool
    {
        if ($sale instanceof CartInterface || $sale instanceof QuoteInterface) {
            return true;
        }

        return false;
    }

    public function getName(): string
    {
        return 'ekyna_commerce_availability';
    }
}
