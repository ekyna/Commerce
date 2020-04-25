<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Event\SubjectUrlEvent;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SubjectHelper
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper implements SubjectHelperInterface
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    protected $registry;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $registry
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(SubjectProviderRegistryInterface $registry, EventDispatcherInterface $eventDispatcher)
    {
        $this->registry = $registry;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function hasSubject(SubjectRelativeInterface $relative): bool
    {
        return null !== $this->resolve($relative, false);
    }

    /**
     * @inheritdoc
     */
    public function resolve(SubjectRelativeInterface $relative, $throw = true): ?SubjectInterface
    {
        if (!$relative->getSubjectIdentity()->hasIdentity()) {
            return null;
        }

        try {
            return $this->getProvider($relative)->resolve($relative);
        } catch (SubjectException $e) {
            if ($throw) {
                throw $e;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function assign(SubjectRelativeInterface $relative, $subject): SubjectProviderInterface
    {
        return $this->getProvider($subject)->assign($relative, $subject);
    }

    /**
     * @inheritdoc
     */
    public function find($provider, $identifier): ?SubjectInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getProvider($provider)->getRepository()->find($identifier);
    }

    /**
     * @inheritdoc
     */
    public function generateAddToCartUrl($subject, bool $path = true): ?string
    {
        return $this->getUrl(SubjectUrlEvent::ADD_TO_CART, $subject, $path);
    }

    /**
     * @inheritdoc
     */
    public function generatePublicUrl($subject, bool $path = true): ?string
    {
        return $this->getUrl(SubjectUrlEvent::PUBLIC, $subject, $path);
    }

    /**
     * @inheritdoc
     */
    public function generateImageUrl($subject, bool $path = true): ?string
    {
        return $this->getUrl(SubjectUrlEvent::IMAGE, $subject, $path);
    }

    /**
     * @inheritdoc
     */
    public function generatePrivateUrl($subject, bool $path = true): ?string
    {
        return $this->getUrl(SubjectUrlEvent::PRIVATE, $subject, $path);
    }

    /**
     * Returns the provider by name or supporting the given relative or subject.
     *
     * @param string|SubjectRelativeInterface|object $nameOrRelativeOrSubject
     *
     * @return SubjectProviderInterface
     *
     * @throws SubjectException
     */
    protected function getProvider($nameOrRelativeOrSubject): SubjectProviderInterface
    {
        if (null === $provider = $this->registry->getProvider($nameOrRelativeOrSubject)) {
            throw new SubjectException('Failed to get provider.');
        }

        return $provider;
    }

    /**
     * Returns the url for the given type and subject.
     *
     * @param string                                    $name
     * @param SubjectRelativeInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return string|null
     */
    protected function getUrl(string $name, $subject, bool $path): ?string
    {
        if ($subject instanceof SubjectRelativeInterface) {
            if (null === $subject = $this->resolve($subject, false)) {
                return null;
            }
        }

        if (!$subject instanceof SubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . SubjectInterface::class);
        }

        // TODO Cache

        $event = new SubjectUrlEvent($subject, $path);

        $this->eventDispatcher->dispatch($name, $event);

        return $event->getUrl();
    }
}
