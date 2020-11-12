<?php

namespace Acme\Product\Provider;

use Acme\Product\Entity\Product;
use Acme\Product\Repository\ProductRepository;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;

/**
 * Class ProductProvider
 * @package Acme\Commerce\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SubjectProviderInterface
{
    const NAME = 'acme_product';

    /**
     * @var ProductRepository
     */
    private $productRepository;


    /**
     * Constructor.
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function assign(Reference $reference, Subject $subject): SubjectProviderInterface
    {
        return $this->transform($subject, $reference->getSubjectIdentity());
    }

    /**
     * @inheritDoc
     */
    public function resolve(Reference $reference): Subject
    {
        return $this->reverseTransform($reference->getSubjectIdentity());
    }

    /**
     * @inheritdoc
     */
    public function transform(Subject $subject, Identity $identity): SubjectProviderInterface
    {
        $this->assertSupportsSubject($subject);

        if ($subject === $identity->getSubject()) {
            return $this;
        }

        /** @var Product $subject */
        $identity
            ->setProvider(static::NAME)
            ->setIdentifier($subject->getId())
            ->setSubject($subject);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform(Identity $identity): Subject
    {
        $this->assertSupportsIdentity($identity);

        $productId = intval($identity->getIdentifier());

        if (null !== $product = $identity->getSubject()) {
            if ((!$product instanceof Product) || ($product->getId() != $productId)) {
                // TODO Clear identity data ?
                throw new SubjectException("Failed to resolve item subject.");
            }

            return $product;
        }

        /** @var Product $product */
        if (null === $product = $this->productRepository->find($productId)) {
            // TODO Clear identity data ?
            throw new SubjectException("Failed to resolve item subject.");
        }

        $identity->setSubject($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function supportsSubject(Subject $subject): bool
    {
        return $subject instanceof Product;
    }

    /**
     * @inheritdoc
     */
    public function supportsReference(Reference $reference): bool
    {
        return $reference->getSubjectIdentity()->getProvider() === self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getRepository(): SubjectRepositoryInterface
    {
        return $this->productRepository;
    }

    /**
     * @inheritdoc
     */
    public function getSubjectClass(): string
    {
        return Product::class;
    }

    /**
     * @inheritDoc
     */
    public function getSearchRouteAndParameters(string $context): array
    {
        return [
            'route'      => 'acme_product_product_admin_search',
            'parameters' => [],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return 'Acme Product';
    }

    /**
     * Asserts that the subject reference is supported.
     *
     * @param Subject $subject
     *
     * @throws SubjectException
     */
    protected function assertSupportsSubject(Subject $subject): void
    {
        if (!$this->supportsSubject($subject)) {
            throw new SubjectException('Unsupported subject.');
        }
    }

    /**
     * Asserts that the subject identity is supported.
     *
     * @param Identity $identity
     *
     * @throws SubjectException
     */
    protected function assertSupportsIdentity(Identity $identity): void
    {
        if ($identity->getProvider() != static::NAME) {
            throw new SubjectException('Unsupported subject identity.');
        }
    }
}
