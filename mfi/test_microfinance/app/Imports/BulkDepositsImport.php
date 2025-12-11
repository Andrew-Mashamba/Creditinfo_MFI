<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class BulkDepositsImport implements ToArray
{
    protected $previewMode = false;

    public function setPreviewMode($value)
    {
        $this->previewMode = $value;
    }

    public function array(array $array)
    {
        // Just return the array for preview
        return $array;
    }
} 