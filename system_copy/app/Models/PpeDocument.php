<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpeDocument extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ppe_id', 'document_type', 'document_name', 'file_path',
        'file_size', 'mime_type', 'description', 'document_date', 'uploaded_by'
    ];
    
    protected $casts = [
        'document_date' => 'date'
    ];
    
    public function ppe()
    {
        return $this->belongsTo(PPE::class, 'ppe_id');
    }
    
    public function getDocumentTypeDisplayAttribute()
    {
        $types = [
            'purchase_invoice' => 'Purchase Invoice',
            'receipt' => 'Receipt',
            'warranty' => 'Warranty Document',
            'manual' => 'User Manual',
            'insurance_policy' => 'Insurance Policy',
            'valuation_report' => 'Valuation Report',
            'maintenance_report' => 'Maintenance Report',
            'inspection_report' => 'Inspection Report',
            'disposal_document' => 'Disposal Document',
            'photo' => 'Photo',
            'other' => 'Other Document'
        ];
        
        return $types[$this->document_type] ?? $this->document_type;
    }
}