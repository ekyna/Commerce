<?php

namespace Ekyna\Component\Commerce\Subject\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Subject\Model\OfferInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class AbstractSubject
 * @package Ekyna\Component\Commerce\Subject\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSubject implements SubjectInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var TaxGroupInterface
     */
    protected $taxGroup;

    /**
     * @var ArrayCollection|OfferInterface[]
     */
    protected $offers;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->offers = new ArrayCollection();
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
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxGroup()
    {
        return $this->taxGroup;
    }

    /**
     * @inheritdoc
     */
    public function setTaxGroup(TaxGroupInterface $taxGroup)
    {
        $this->taxGroup = $taxGroup;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasOffers()
    {
        return 0 < $this->offers->count();
    }

    /**
     * @inheritdoc
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @inheritdoc
     */
    public function hasOffer(OfferInterface $offer)
    {
        return $this->offers->contains($offer);
    }

    /**
     * @inheritdoc
     */
    public function addOffer(OfferInterface $offer)
    {
        if (!$this->hasOffer($offer)) {
            $offer->setSubject($this);
            $this->offers->add($offer);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOffer(OfferInterface $offer)
    {
        if ($this->hasOffer($offer)) {
            $offer->setSubject(null);
            $this->offers->removeElement($offer);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOffers($offers)
    {
        $this->offers = $offers;

        return $this;
    }
}
