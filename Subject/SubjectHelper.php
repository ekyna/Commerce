<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;

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
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $registry
     */
    public function __construct(SubjectProviderRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(SubjectRelativeInterface $relative, $throw = true)
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
     * {@inheritdoc}
     */
    public function assign(SubjectRelativeInterface $relative, $subject)
    {
        return $this->getProvider($subject)->assign($relative, $subject);
    }

    /**
     * Returns the provider by name or supporting the given relative or subject.
     *
     * @param string|SubjectRelativeInterface|object $nameOrRelativeOrSubject
     *
     * @return \Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface
     * @throws SubjectException
     */
    protected function getProvider($nameOrRelativeOrSubject)
    {
        if (null === $provider = $this->registry->getProvider($nameOrRelativeOrSubject)) {
            throw new SubjectException('Failed to get provider.');
        }

        return $provider;
    }
}
