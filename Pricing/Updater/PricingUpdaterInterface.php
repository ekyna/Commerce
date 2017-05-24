<?php

namespace Ekyna\Component\Commerce\Pricing\Updater;

use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectInterface;

/**
 * Interface PricingUpdaterInterface
 * @package Ekyna\Component\Commerce\Pricing\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PricingUpdaterInterface
{
    /**
     * Updates the vat number subject's fields.
     *
     * @param VatNumberSubjectInterface $subject
     *
     * @return bool Whether the subject's fields has been changed
     */
    public function updateVatNumberSubject(VatNumberSubjectInterface $subject);
}
