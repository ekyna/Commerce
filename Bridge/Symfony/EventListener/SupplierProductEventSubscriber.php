<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Event\SupplierProductEvents;
use Ekyna\Component\Commerce\Supplier\EventListener\SupplierProductListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SupplierProductEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductEventSubscriber extends SupplierProductListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     * @param RequestStack           $requestStack
     */
    public function __construct(SubjectHelperInterface $subjectHelper, RequestStack $requestStack)
    {
        parent::__construct($subjectHelper);

        $this->requestStack = $requestStack;
    }

    /**
     * @inheritDoc
     */
    public function onInitialize(ResourceEventInterface $event): void
    {
        $request = $this->requestStack->getMasterRequest();

        if (!$request) {
            parent::onInitialize($event);

            return;
        }

        $provider = $request->query->get('provider');
        $identifier = $request->query->get('identifier');

        if (empty($provider) || empty($identifier)) {
            parent::onInitialize($event);

            return;
        }

        $product = $this->getSupplierProductFromEvent($event);
        $product
            ->getSubjectIdentity()
            ->setProvider($provider)
            ->setIdentifier($identifier);

        parent::onInitialize($event);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SupplierProductEvents::INITIALIZE => ['onInitialize', 0],
        ];
    }
}
