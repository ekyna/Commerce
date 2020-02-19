<?php

namespace Ekyna\Component\Commerce\Newsletter\Webhook;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface HandlerInterface
 * @package Ekyna\Component\Commerce\Newsletter\Webhook
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface HandlerInterface
{
    /**
     * Handles the webhook request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request): Response;

    /**
     * Returns the webhook handler name.
     *
     * @return string
     */
    public static function getName(): string;
}
