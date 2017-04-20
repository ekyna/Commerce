<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Updater;

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
    private PersistenceHelperInterface $persistenceHelper;
    private AudienceRepositoryInterface $audienceRepository;

    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        AudienceRepositoryInterface $audienceRepository
    ) {
        $this->persistenceHelper  = $persistenceHelper;
        $this->audienceRepository = $audienceRepository;
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
