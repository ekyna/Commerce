<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;

/**
 * Class Helper
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Helper implements HelperInterface
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
    public function resolve(SubjectRelativeInterface $relative)
    {
        if (null === $subject = $relative->getSubject()) {
            if ($relative->hasSubjectData()) {
                $subject = $this->getProvider($relative)->resolve($relative);
                $relative->setSubject($subject);
            }
        }

        return $subject;
    }

    /**
     * Returns the provider that supports the subject relative.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return \Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface
     * @throws InvalidArgumentException
     */
    protected function getProvider(SubjectRelativeInterface $relative)
    {
        if (null === $provider = $this->registry->getProviderByRelative($relative)) {
            throw new InvalidArgumentException('Unsupported subject relative.');
        }

        return $provider;
    }
}
