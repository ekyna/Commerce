<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Resolver\SubjectResolverRegistryInterface;

/**
 * Class Helper
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Helper implements HelperInterface
{
    /**
     * @var SubjectResolverRegistryInterface
     */
    protected $registry;


    /**
     * Constructor.
     *
     * @param SubjectResolverRegistryInterface $registry
     */
    public function __construct(SubjectResolverRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(SaleItemInterface $item)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        if ((null === $subject = $item->getSubject()) && $item->hasSubjectIdentity()) {
            $subject = $this->getResolver($item)->resolve($item);
            /** @noinspection PhpInternalEntityUsedInspection */
            $item->setSubject($subject);

            return $subject;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFrontOfficePath(SaleItemInterface $item)
    {
        if (!$item->hasSubjectIdentity()) {
            return null;
        }

        if (null !== $resolver = $this->getResolver($item)) {
            return $resolver->generateFrontOfficePath($item);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generateBackOfficePath(SaleItemInterface $item)
    {
        if (!$item->hasSubjectIdentity()) {
            return null;
        }

        if (null !== $resolver = $this->getResolver($item)) {
            return $resolver->generateBackOfficePath($item);
        }

        return null;
    }

    /**
     * Returns the resolver that supports the item.
     *
     * @param SaleItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Subject\Resolver\SubjectResolverInterface
     * @throws InvalidArgumentException
     */
    protected function getResolver(SaleItemInterface $item)
    {
        foreach ($this->registry->getResolvers() as $resolver) {
            if ($resolver->supports($item)) {
                return $resolver;
            }
        }

        throw new InvalidArgumentException('Unsupported subject.');
    }
}
