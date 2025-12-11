<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class BulkMembersImport implements ToArray
{
    protected $previewMode = false;

    public function setPreviewMode($value)
    {
        $this->previewMode = $value;
    }

    public function array(array $array)
    {
        // Just return the array for processing
        return $array;
    }
} 