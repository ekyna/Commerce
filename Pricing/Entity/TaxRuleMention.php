<?php

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
    /**
     * @var TaxRuleInterface|null
     */
    protected $taxRule;


    /**
     * Returns the tax rule.
     *
     * @return TaxRuleInterface|null
     */
    public function getTaxRule(): ?TaxRuleInterface
    {
        return $this->taxRule;
    }

    /**
     * Sets the tax rule.
     *
     * @param TaxRuleInterface|null $taxRule
     *
     * @return TaxRuleMention
     */
    public function setTaxRule(TaxRuleInterface $taxRule = null): TaxRuleMention
    {
        if ($this->taxRule !== $taxRule) {
            if ($previous = $this->taxRule) {
                $this->taxRule = null;
                $previous->removeMention($this);
            }

            if ($this->taxRule = $taxRule) {
                $this->taxRule->addMention($this);
            }
        }

        return $this;
    }
}
