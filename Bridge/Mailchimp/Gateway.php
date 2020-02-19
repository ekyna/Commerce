<?php

namespace Ekyna\Component\Commerce\Bridge\Mailchimp;

use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;

/**
 * Class Gateway
 * @package Ekyna\Component\Commerce\Bridge\Mailchimp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Gateway implements GatewayInterface
{
    /**
     * @var Api
     */
    private $api;


    /**
     * Constructor.
     *
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function createAudience(AudienceInterface $audience): bool
    {
        throw new NewsletterException("Unsupported operation");
    }

    /**
     * @inheritDoc
     */
    public function updateAudience(AudienceInterface $audience, array $changeSet): bool
    {
        $map = $this->buildPatchMap($changeSet, [
            'name' => null,
        ]);

        if (empty($map)) {
            return false;
        }

        $result = $this->api->patch("lists/{$audience->getIdentifier()}", $map);

        if (!$this->api->success()) {
            $this->api->logError($result);

            throw new NewsletterException('Failed to update audience (' . $this->api->getLastError() . ')');
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteAudience(AudienceInterface $audience): bool
    {
        throw new NewsletterException("Unsupported operation");
    }

    /**
     * @inheritDoc
     */
    public function createMember(MemberInterface $member): bool
    {
        $audienceId = $member->getAudience()->getIdentifier();

        $data = [
            'email_address' => $member->getEmail(),
            'status'        => $member->getStatus(),
        ];

        if (!empty($attributes = $member->getAttributes())) {
            $data['merge_fields'] = $attributes;
        }

        $result = $this->api->post("lists/$audienceId/members", $data);

        if (!$this->api->success()) {
            $this->api->logError($result);

            throw new NewsletterException('Failed to create member (' . $this->api->getLastError() . ')');
        }

        $member->setIdentifier($result['web_id']);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function updateMember(MemberInterface $member, array $changeSet): bool
    {
        $return = false;

        // Changing email is not allowed
        if (isset($changeSet['email'])) {
            // Restore previous
            $member->setEmail($changeSet['email'][0]);
            $return = true;
        }

        $map = $this->buildPatchMap($changeSet, [
            'status'     => null,
            'attributes' => 'merge_fields',
        ]);

        if (empty($map)) {
            return $return;
        }

        $map['emailAddress'] = $member->getEmail();

        $audienceId = $member->getAudience()->getIdentifier();
        $hash = $this->api->subscriberHash($member->getEmail());

        $result = $this->api->patch("lists/$audienceId/members/$hash", $map);

        if (!$this->api->success()) {
            $this->api->logError($result);

            throw new NewsletterException('Failed to update member (' . $this->api->getLastError() . ')');
        }

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function deleteMember(MemberInterface $member): bool
    {
        $audienceId = $member->getAudience()->getIdentifier();
        $hash       = $this->api->subscriberHash($member->getEmail());

        $result = $this->api->delete("lists/$audienceId/members/$hash");

        if (!$this->api->success()) {
            $this->api->logError($result);

            throw new NewsletterException('Failed to delete member (' . $this->api->getLastError() . ')');
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $action): bool
    {
        return in_array($action, [
            self::UPDATE_AUDIENCE,
            self::CREATE_MEMBER,
            self::UPDATE_MEMBER,
            self::DELETE_MEMBER,
        ], true);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return Constants::NAME;
    }

    /**
     * Builds the patch map.
     *
     * @param array $changeSet
     * @param array $properties
     *
     * @return array
     */
    protected function buildPatchMap(array $changeSet, array $properties): array
    {
        $map = [];

        foreach ($properties as $property => $field) {
            $field = $field ?? $property;
            if (isset($changeSet[$property])) {
                $map[$field] = $changeSet[$property][1];
            }
        }

        return $map;
    }
}
