<?php

namespace App\Services;

interface ExportInterface
{
    public function export(array $data): void;
}
