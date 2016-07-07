<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;

/**
 * Interface SubjectInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     * @return $this|SubjectInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     * @return $this|SubjectInterface
     */
    public function setReference($reference);

    /**
     * Returns the taxGroup.
     *
     * @return TaxGroupInterface
     */
    public function getTaxGroup();

    /**
     * Sets the taxGroup.
     *
     * @param TaxGroupInterface $taxGroup
     * @return $this|SubjectInterface
     */
    public function setTaxGroup(TaxGroupInterface $taxGroup);

    /**
     * Returns whether the subject has at least one offer or not.
     *
     * @return boolean
     */
    public function hasOffers();

    /**
     * Returns the offers.
     *
     * @return ArrayCollection|OfferInterface[]
     */
    public function getOffers();

    /**
     * Returns whether the subject has the offer or not.
     *
     * @param OfferInterface $offer
     * @return bool
     */
    public function hasOffer(OfferInterface $offer);

    /**
     * Adds the offer.
     *
     * @param OfferInterface $offer
     * @return $this|SubjectInterface
     */
    public function addOffer(OfferInterface $offer);

    /**
     * Removes the offer.
     *
     * @param OfferInterface $offer
     * @return $this|SubjectInterface
     */
    public function removeOffer(OfferInterface $offer);

    /**
     * Sets the offers.
     *
     * @param ArrayCollection|OfferInterface[] $offers
     * @return $this|SubjectInterface
     */
    public function setOffers($offers);
}
