<?php

namespace App\Enums;

enum StatusArticle: string
{
    case PENDING = 'pending';
    case PROCESSED = 'processed';
    case SKIPPED = 'skipped';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::PROCESSED => 'Procesado',
            self::SKIPPED => 'Omitido',
            self::FAILED => 'Fallido',
        };
    }
}
