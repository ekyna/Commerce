<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use function strtoupper;

/**
 * Enum Incoterm
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
enum Incoterm: string
{
    case EXW = 'exw';
    case FCA = 'fca';
    case CPT = 'cpt';
    case CIP = 'cip';
    case DAP = 'dap';
    case DPU = 'dpu';
    case DDP = 'ddp';
    case FAS = 'fas';
    case FOB = 'fob';
    case CFR = 'cfr';

    public function code(): string
    {
        return strtoupper($this->value);
    }
}
