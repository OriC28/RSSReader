<?php

namespace App\Enums;

enum CategoryArticle: string
{
    case TECHNOLOGY = 'Tecnología';
    case SCIENCE = 'Ciencia';
    case BUSINESS = 'Negocios';
    case CULTURE = 'Cultura';
    case SPORTS = 'Deportes';
    case OTHER = 'Otros';
}
