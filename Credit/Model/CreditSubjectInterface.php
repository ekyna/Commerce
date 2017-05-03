<?php

namespace Ekyna\Component\Commerce\Credit\Model;

/**
 * Interface CreditSubjectInterface
 * @package Ekyna\Component\Commerce\Credit\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CreditSubjectInterface
{
    /**
     * Returns whether the order has credits or not.
     *
     * @return bool
     */
    public function hasCredits();

    /**
     * Returns the credits.
     *
     * @return \Doctrine\Common\Collections\Collection|CreditInterface[]
     */
    public function getCredits();

    /**
     * Returns whether the order has the credit or not.
     *
     * @param CreditInterface $credit
     *
     * @return bool
     */
    public function hasCredit(CreditInterface $credit);

    /**
     * Adds the credit.
     *
     * @param CreditInterface $credit
     *
     * @return $this|CreditSubjectInterface
     */
    public function addCredit(CreditInterface $credit);

    /**
     * Removes the credit.
     *
     * @param CreditInterface $credit
     *
     * @return $this|CreditSubjectInterface
     */
    public function removeCredit(CreditInterface $credit);
}
