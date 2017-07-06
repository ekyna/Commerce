<?php

namespace Ekyna\Component\Commerce\Customer\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Pricing\Updater\PricingUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class CustomerListener
 * @package Ekyna\Component\Commerce\Customer\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var PricingUpdaterInterface
     */
    protected $pricingUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param NumberGeneratorInterface   $numberGenerator
     * @param PricingUpdaterInterface    $pricingUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        NumberGeneratorInterface $numberGenerator,
        PricingUpdaterInterface $pricingUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->numberGenerator = $numberGenerator;
        $this->pricingUpdater = $pricingUpdater;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $customer = $this->getCustomerFromEvent($event);

        $this->updateChildrenCompanyName($customer);

        $changed = $this->generateNumber($customer);

        $changed |= $this->updateCompanyNameFromParent($customer);

        $changed |= $this->pricingUpdater->updateVatNumberSubject($customer);

        // TODO
        // - Must have an invoice address

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($customer);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $customer = $this->getCustomerFromEvent($event);

        $changed = $this->generateNumber($customer);

        $changeSet = $this->persistenceHelper->getChangeSet($customer);

        if (array_key_exists('parent', $changeSet)) {
            $changed |= $this->updateCompanyNameFromParent($customer);
        }

        if (array_key_exists('company', $changeSet)) {
            $childrenToPersist = $this->updateChildrenCompanyName($customer);
            foreach ($childrenToPersist as $child) {
                $this->persistenceHelper->persistAndRecompute($child, true);
            }
        }

        $changed |= $this->pricingUpdater->updateVatNumberSubject($customer);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($customer);
        }
    }

    /**
     * Generates the number.
     *
     * @param CustomerInterface $customer
     *
     * @return bool Whether the customer number has been generated or not.
     */
    private function generateNumber(CustomerInterface $customer)
    {
        if (0 == strlen($customer->getNumber())) {
            $this->numberGenerator->generate($customer);

            return true;
        }

        return false;
    }

    /**
     * Updates the customer company name from his parent.
     *
     * @param CustomerInterface $customer
     *
     * @return bool Whether the company name has been changed or not.
     */
    private function updateCompanyNameFromParent(CustomerInterface $customer)
    {
        if ($customer->hasParent()) {
            if (0 === strlen($company = $customer->getParent()->getCompany())) {
                throw new RuntimeException('Parent company name must be set first.');
            }

            if ($company != $customer->getCompany()) {
                $customer->setCompany($company);

                return true;
            }
        }

        return false;
    }

    /**
     * Updates the customer's children company name.
     *
     * @param CustomerInterface $customer
     *
     * @return array The modified children.
     */
    private function updateChildrenCompanyName(CustomerInterface $customer)
    {
        $changedChildren = [];

        if ($customer->hasChildren()) {
            if (0 === strlen($company = $customer->getCompany())) {
                throw new RuntimeException('Company name must be set first.');
            }

            foreach ($customer->getChildren() as $child) {
                if ($company != $child->getCompany()) {
                    $child->setCompany($company);

                    $changedChildren[] = $child;
                }
            }
        }

        return $changedChildren;
    }

    /**
     * Returns the customer from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return CustomerInterface
     * @throws InvalidArgumentException
     */
    private function getCustomerFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CustomerInterface) {
            throw new InvalidArgumentException('Expected CustomerInterface');
        }

        return $resource;
    }
}
