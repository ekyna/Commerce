<?php

namespace Ekyna\Component\Commerce\Subject\Builder;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface FormBuilderInterface
 * @package Ekyna\Component\Commerce\Subject\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @deprecated Use event system
 */
interface FormBuilderInterface
{
    /**
     * Builds the subject item form.
     *
     * @param mixed $form
     * @param SaleItemInterface $item
     *
     * @deprecated Use event system
     */
    public function buildItemForm($form, SaleItemInterface $item);
}
