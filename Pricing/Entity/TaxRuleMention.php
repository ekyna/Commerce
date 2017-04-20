<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractMention;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;

/**
 * Class TaxRuleMention
 * @package Ekyna\Component\Commerce\Pricing\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleMention extends AbstractMention
{
    protected ?TaxRuleInterface $taxRule = null;


    public function getTaxRule(): ?TaxRuleInterface
    {
        return $this->taxRule;
    }

    public function setTaxRule(?TaxRuleInterface $taxRule): TaxRuleMention
    {
        if ($this->taxRule === $taxRule) {
            return $this;
        }

        if ($previous = $this->taxRule) {
            $this->taxRule = null;
            $previous->removeMention($this);
        }

        if ($this->taxRule = $taxRule) {
            $this->taxRule->addMention($this);
        }

        return $this;
    }
}
