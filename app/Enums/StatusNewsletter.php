<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StatusNewsletter: string implements HasLabel
{
    case GENERATING = 'generating';
    case SENT = 'sent';
    case FAILED = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::GENERATING => 'Generando',
            self::SENT => 'Enviado',
            self::FAILED => 'Fallido',
        };
    }
}
