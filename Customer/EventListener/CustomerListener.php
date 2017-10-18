<?php

namespace Ekyna\Component\Commerce\Customer\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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

        $changed = $this->generateNumber($customer);

        $changed |= $this->updateFromParent($customer);

        $changed |= $this->pricingUpdater->updateVatNumberSubject($customer);

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
            $changed |= $this->updateFromParent($customer);
        }

        $changed |= $this->pricingUpdater->updateVatNumberSubject($customer);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($customer);
        }

        $hierarchyFields = ['company', 'customerGroup', 'vatNumber', 'vatDetails', 'vatValid'];
        if ($this->persistenceHelper->isChanged($customer, $hierarchyFields)) {
            $this->scheduleParentChangeEvents($customer);
        }
    }

    /**
     * "Parent has changed" event handler.
     *
     * @param CustomerInterface $customer
     */
    public function onParentChange(CustomerInterface $customer)
    {
        if ($this->updateFromParent($customer)) {
            $this->persistenceHelper->persistAndRecompute($customer, true);
        }
    }

    /**
     * Updates from parent.
     *
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    protected function updateFromParent(CustomerInterface $customer)
    {
        if (!$customer->hasParent()) {
            // Make sure default invoice and delivery address exists.
            if (null === $customer->getDefaultInvoiceAddress()) {
                /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
                if (null !== $address = $customer->getAddresses()->first()) {
                    $address->setInvoiceDefault(true);
                    $this->persistenceHelper->persistAndRecompute($address, false);
                }
            }
            if (null === $customer->getDefaultDeliveryAddress()) {
                /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
                if (null !== $address = $customer->getAddresses()->first()) {
                    $address->setDeliveryDefault(true);
                    $this->persistenceHelper->persistAndRecompute($address, false);
                }
            }
            return false;
        }

        $parent = $customer->getParent();
        $changed = false;

        // Company
        if (empty($customer->getCompany())) {
            $company = $parent->getCompany();
            if ($company != $customer->getCompany()) {
                $customer->setCompany($company);
                $changed = true;
            }
        }

        // Customer group
        $group = $parent->getCustomerGroup();
        if ($group !== $customer->getCustomerGroup()) {
            $customer->setCustomerGroup($group);
            $changed = true;
        }

        // Clear VAT info
        if (!empty($customer->getVatNumber())) {
            $customer->setVatNumber(null);
            $changed = true;
        }
        if (!empty($customer->getVatDetails())) {
            $customer->setVatDetails([]);
            $changed = true;
        }
        if ($customer->isVatValid()) {
            $customer->setVatValid(false);
            $changed = true;
        }

        // Clear payment term
        if (null !== $customer->getPaymentTerm()) {
            $customer->setPaymentTerm(null);
            $changed = true;
        }
        // Clear outstanding
        if (0 !== $customer->getOutstandingLimit()) {
            $customer->setOutstandingLimit(0);
            $changed = true;
        }
        // TODO (?) Clear balance
        /*if (0 !== $customer->getOutstandingBalance()) {
            $customer->setOutstandingBalance(0);
            $changed = true;
        }*/

        return $changed;
    }

    /**
     * Schedules the parent change events.
     *
     * @param CustomerInterface $customer
     */
    protected function scheduleParentChangeEvents(CustomerInterface $customer)
    {
        if (!$customer->hasChildren()) {
            return;
        }

        foreach ($customer->getChildren() as $child) {
            $this->persistenceHelper->scheduleEvent(CustomerEvents::PARENT_CHANGE, $child);
        }
    }

    /**
     * Returns the customer from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return CustomerInterface
     * @throws InvalidArgumentException
     */
    protected function getCustomerFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CustomerInterface) {
            throw new InvalidArgumentException('Expected instance of ' . CustomerInterface::class);
        }

        return $resource;
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
}
