<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
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
    public function resolve(OrderItemInterface $item)
    {
        if ((null === $subject = $item->getSubject()) && $item->hasSubjectIdentity()) {
            $subject = $this->getResolver($item)->resolve($item);
            $item->setSubject($subject);

            return $subject;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(SubjectInterface $subject)
    {
        return $this->getResolver($subject)->transform($subject);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFrontOfficePath($subjectOrItem)
    {
        if ($subjectOrItem instanceof OrderItemInterface && !$subjectOrItem->hasSubjectIdentity()) {
            return null;
        }

        if (null !== $resolver = $this->getResolver($subjectOrItem)) {
            return $resolver->generateFrontOfficePath($subjectOrItem);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generateBackOfficePath($subjectOrItem)
    {
        if ($subjectOrItem instanceof OrderItemInterface && !$subjectOrItem->hasSubjectIdentity()) {
            return null;
        }

        if (null !== $resolver = $this->getResolver($subjectOrItem)) {
            return $resolver->generateBackOfficePath($subjectOrItem);
        }

        return null;
    }

    /**
     * Returns the resolver that supports the subject or item.
     *
     * @param SubjectInterface|OrderItemInterface $subjectOrItem
     *
     * @return \Ekyna\Component\Commerce\Subject\Resolver\SubjectResolverInterface
     * @throws InvalidArgumentException
     */
    protected function getResolver($subjectOrItem)
    {
        foreach ($this->registry->getResolvers() as $resolver) {
            if ($resolver->supports($subjectOrItem)) {
                return $resolver;
            }
        }

        throw new InvalidArgumentException('Unsupported subject.');
    }
}
