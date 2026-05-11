<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class Comment
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Comment
{
    public function __construct(
        public readonly string  $content,
        public readonly ?string $theme = null,
        public readonly ?string $icon = null,
        public readonly string $type = 'text', // label, alert
    ) {
    }

    public function html(): string
    {
        $output = '';
        if (!empty($this->icon)) {
            $output = "<i class=\"$this->icon\"></i>&nbsp;";
        }
        $output .= $this->content;
        if (!empty($this->theme)) {
            $class = match ($this->type) {
                'label' => 'label label',
                'alert' => 'alert alert',
                default => 'text',
            };
            return "<div class=\"$class-$this->theme\">$output</div>";
        }

        return $output;
    }
}
