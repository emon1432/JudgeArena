<?php

namespace App\Core\Contracts\Importers;

interface SubmissionImporter
{
    public function import(): array;
}
