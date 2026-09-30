<?php

namespace App\Enums;

enum StatusNewsletter: string
{
    case GENERATING = 'generating';
    case SENT = 'sent';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::GENERATING => 'Generando',
            self::SENT => 'Enviado',
            self::FAILED => 'Fallido',
        };
    }
}
