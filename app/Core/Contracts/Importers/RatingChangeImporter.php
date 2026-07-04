<?php

namespace App\Core\Contracts\Importers;

interface RatingChangeImporter
{
    public function import(): array;
}
