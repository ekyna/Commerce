<?php

namespace Ekyna\Component\Commerce\Newsletter\Updater;

use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AudienceUpdater
 * @package Ekyna\Component\Commerce\Newsletter\Updater
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceUpdater
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var AudienceRepositoryInterface
     */
    private $audienceRepository;

    /**
     * @var GeneratorInterface
     */
    private $keyGenerator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface  $persistenceHelper
     * @param AudienceRepositoryInterface $audienceRepository
     * @param GeneratorInterface $keyGenerator
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        AudienceRepositoryInterface $audienceRepository,
        GeneratorInterface $keyGenerator
    ) {
        $this->persistenceHelper  = $persistenceHelper;
        $this->audienceRepository = $audienceRepository;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * Generates the audience key.
     *
     * @param AudienceInterface $audience
     *
     * @return bool Whether the audience has been changed.
     */
    public function generateKey(AudienceInterface $audience): bool
    {
        if (!empty($audience->getKey())) {
            return false;
        }

        $audience->setKey($this->keyGenerator->generate($audience));

        return true;
    }

    /**
     * Fixes the default audience.
     *
     * @param AudienceInterface $audience
     */
    public function fixDefault(AudienceInterface $audience): void
    {
        if (!$this->persistenceHelper->isChanged($audience, ['default'])) {
            return;
        }

        $this->audienceRepository->purgeDefault();

        if ($audience->isDefault()) {
            if (!$audience->isPublic()) {
                $audience->setPublic(true);
                $this->persistenceHelper->persistAndRecompute($audience, false);
            }

            try {
                $previousGroup = $this->audienceRepository->findDefault();
            } catch (RuntimeException $e) {
                return;
            }

            if ($previousGroup === $audience) {
                return;
            }

            $previousGroup->setDefault(false);

            $this->persistenceHelper->persistAndRecompute($previousGroup, false);

            return;
        }

        try {
            $this->audienceRepository->findDefault();
        } catch (RuntimeException $e) {
            $audience
                ->setDefault(true)
                ->setPublic(true);

            $this->persistenceHelper->persistAndRecompute($audience, false);
        }
    }
}
