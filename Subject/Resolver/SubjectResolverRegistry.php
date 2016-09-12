<?php

namespace Ekyna\Component\Commerce\Subject\Resolver;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class SubjectResolverRegistry
 * @package Ekyna\Component\Commerce\Subject\Resolver
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectResolverRegistry implements SubjectResolverRegistryInterface
{
    /**
     * @var array|SubjectResolverInterface[]
     */
    protected $resolvers;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->resolvers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function addResolver(SubjectResolverInterface $resolver)
    {
        if (array_key_exists($resolver->getName(), $this->resolvers)) {
            throw new \RuntimeException(sprintf('Subject resolver "%s" is already registered.', $resolver->getName()));
        }

        $this->resolvers[$resolver->getName()] = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getResolverByItem(SaleItemInterface $item)
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supportsItem($item)) {
                return $resolver;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResolvers()
    {
        return $this->resolvers;
    }
}
