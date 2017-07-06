<?php

namespace Ekyna\Component\Commerce\Pricing\Updater;

use Ekyna\Component\Commerce\Pricing\Api\PricingApiInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectInterface;

/**
 * Class PricingUpdater
 * @package Ekyna\Component\Commerce\Pricing\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingUpdater implements PricingUpdaterInterface
{
    /**
     * @var PricingApiInterface
     */
    private $pricingApi;


    /**
     * Constructor.
     *
     * @param PricingApiInterface $pricingApi
     */
    public function __construct(PricingApiInterface $pricingApi)
    {
        $this->pricingApi = $pricingApi;
    }

    /**
     * @inheritDoc
     */
    public function updateVatNumberSubject(VatNumberSubjectInterface $subject)
    {
        $changed = false;
        $valid = $subject->isVatValid();

        if (0 < strlen($number = $subject->getVatNumber())) {
            if (!$valid || empty($subject->getVatDetails())) {
                if (null !== $result = $this->pricingApi->validateVatNumber($number)) {
                    if ($valid = $result->isValid()) {
                        $subject->setVatDetails($result->getDetails());
                    }
                }
            }
        } else {
            $valid = false;
        }

        if ($valid != $subject->isVatValid()) {
            $subject->setVatValid($valid);
            $changed = true;
        }

        if (!$valid && !empty($subject->getVatDetails())) {
            $subject->setVatDetails([]);
            $changed = true;
        }


        return $changed;
    }
}
