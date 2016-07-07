<?php

namespace Ekyna\Component\Commerce\Subject\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Pricing\Model\PriceListInterface;
use Ekyna\Component\Commerce\Subject\Model\OfferInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class Offer
 * @package Ekyna\Component\Commerce\Subject\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Offer implements OfferInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var SubjectInterface
     */
    protected $subject;

    /**
     * @var float
     */
    protected $netPrice;

    /**
     * @var int
     */
    protected $minQuantity;

    /**
     * @var \DateTime
     */
    protected $startAt;

    /**
     * @var \DateTime
     */
    protected $endAt;

    /**
     * @var ArrayCollection|PriceListInterface[]
     */
    protected $priceLists;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->priceLists = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject(SubjectInterface $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setMinQuantity($minQuantity)
    {
        $this->minQuantity = $minQuantity;
        return $this;
    }

    /**
     * Returns the "start at" date.
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Sets the "start at" date.
     *
     * @param \DateTime $startAt
     * @return Offer
     */
    public function setStartAt(\DateTime $startAt = null)
    {
        $this->startAt = $startAt;
        return $this;
    }

    /**
     * Returns the "end at" date.
     *
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * Sets the "end at" date.
     *
     * @param \DateTime $endAt
     * @return Offer
     */
    public function setEndAt(\DateTime $endAt = null)
    {
        $this->endAt = $endAt;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasPriceLists()
    {
        return 0 < $this->priceLists->count();
    }

    /**
     * @inheritdoc
     */
    public function getPriceLists()
    {
        return $this->priceLists;
    }

    /**
     * @inheritdoc
     */
    public function hasPriceList(PriceListInterface $priceList)
    {
        return $this->priceLists->contains($priceList);
    }

    /**
     * @inheritdoc
     */
    public function addPriceList(PriceListInterface $priceList)
    {
        if (!$this->hasPriceList($priceList)) {
            $this->priceLists->add($priceList);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePriceList(PriceListInterface $priceList)
    {
        if ($this->hasPriceList($priceList)) {
            $this->priceLists->removeElement($priceList);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPriceLists(ArrayCollection $priceLists)
    {
        $this->priceLists = $priceLists;
        return $this;
    }
}
