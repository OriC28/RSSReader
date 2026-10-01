<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CategoryArticle: string implements HasColor, HasLabel
{
    case TECHNOLOGY = 'Tecnología';
    case SCIENCE = 'Ciencia';
    case BUSINESS = 'Negocios';
    case CULTURE = 'Cultura';
    case SPORTS = 'Deportes';
    case OTHER = 'Otros';

    public function getLabel(): string
    {
        return match ($this) {
            self::TECHNOLOGY => 'Tecnología',
            self::SCIENCE => 'Ciencia',
            self::BUSINESS => 'Negocio',
            self::CULTURE => 'Cultura',
            self::SPORTS => 'Deportes',
            self::OTHER => 'Otros',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TECHNOLOGY => 'info',
            self::SCIENCE => 'success',
            self::BUSINESS => 'warning',
            self::CULTURE => 'gray',
            self::SPORTS => 'primary',
            self::OTHER => 'gray',
        };
    }
}
