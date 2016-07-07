<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Pricing\Model\PriceListInterface;

/**
 * Interface OfferInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OfferInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the subject.
     *
     * @return SubjectInterface
     */
    public function getSubject();

    /**
     * Sets the subject.
     *
     * @param SubjectInterface $subject
     * @return $this|OfferInterface
     */
    public function setSubject(SubjectInterface $subject);

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     * @return $this|OfferInterface
     */
    public function setNetPrice($netPrice);

    /**
     * Returns the minimum quantity.
     *
     * @return int
     */
    public function getMinQuantity();

    /**
     * Sets the minimum quantity.
     *
     * @param int $minQuantity
     * @return $this|OfferInterface
     */
    public function setMinQuantity($minQuantity);

    /**
     * Returns the "start at" date.
     *
     * @return \DateTime
     */
    public function getStartAt();

    /**
     * Sets the "start at" date.
     *
     * @param \DateTime $startAt
     * @return $this|OfferInterface
     */
    public function setStartAt(\DateTime $startAt = null);

    /**
     * Returns the "end at" date.
     *
     * @return \DateTime
     */
    public function getEndAt();

    /**
     * Sets the "end at" date.
     *
     * @param \DateTime $endAt
     * @return $this|OfferInterface
     */
    public function setEndAt(\DateTime $endAt = null);

    /**
     * Returns whether the offer has at least one price lists or not.
     *
     * @return boolean
     */
    public function hasPriceLists();

    /**
     * Returns the price lists.
     *
     * @return ArrayCollection|PriceListInterface[]
     */
    public function getPriceLists();

    /**
     * Returns whether the offer has the price list or not.
     *
     * @param PriceListInterface $priceList
     * @return bool
     */
    public function hasPriceList(PriceListInterface $priceList);

    /**
     * Adds the price list.
     *
     * @param PriceListInterface $priceList
     * @return $this|OfferInterface
     */
    public function addPriceList(PriceListInterface $priceList);

    /**
     * Removes the price list.
     *
     * @param PriceListInterface $priceList
     * @return $this|OfferInterface
     */
    public function removePriceList(PriceListInterface $priceList);

    /**
     * Sets the price lists.
     *
     * @param ArrayCollection|PriceListInterface[] $priceLists
     * @return $this|OfferInterface
     */
    public function setPriceLists(ArrayCollection $priceLists);
}
