<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusArticle: string implements HasColor, HasLabel
{
    case PENDING = 'pending';
    case PROCESSED = 'processed';
    case SKIPPED = 'skipped';
    case FAILED = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::PROCESSED => 'Procesado',
            self::SKIPPED => 'Omitido',
            self::FAILED => 'Fallido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSED => 'success',
            self::SKIPPED => 'gray',
            self::FAILED => 'danger',
        };
    }
}
