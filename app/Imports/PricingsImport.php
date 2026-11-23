<?php

namespace App\Imports;

use App\Models\ProjectPricing;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Database\Eloquent\Model;

class PricingsImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function model(array $row): ?Model
    {
        // 1. Empty row skip
        if (!isset($row['flat_no']) || $row['flat_no'] == null) {
            return null; 
        }

        // 2. 🚨 "TOTAL" ROW SKIP PANDROM 🚨
        // Excel la "Total", "Grand Total" nu irundha atha skip panniduvom
        $flatNo = strtolower(trim($row['flat_no']));
        if ($flatNo === 'total' || $flatNo === 'grand total' || $flatNo === 'sub total') {
            return null; // Skip this row
        }

        // 3. Missing required fields ku default value kudukkirom (Safety)
        // Illa na ipo irukka mathiri DB error varum
        $floor = isset($row['floor']) && $row['floor'] != null ? $row['floor'] : 'N/A';
        $type = isset($row['type']) && $row['type'] != null ? $row['type'] : 'N/A';
        $facing = isset($row['facing']) && $row['facing'] != null ? $row['facing'] : 'N/A';

        // --- AREA & PRICE FIX ---
        $area = isset($row['area']) ? round((float)$row['area']) : 0; 
        
        $offerPrice = isset($row['offer_price']) ? round((float)$row['offer_price']) : 0;
        $originalPrice = isset($row['original_price']) ? round((float)$row['original_price']) : 0;

        return new ProjectPricing([
            'project_id'     => $this->projectId,
            'flat_no'        => $row['flat_no'],
            'floor'          => $floor,     // Default 'N/A'
            'type'           => $type,      // Default 'N/A'
            'facing'         => $facing,    // Default 'N/A'
            'area'           => (string)$area, 
            'offer_price'    => (string)$offerPrice,
            'original_price' => (string)$originalPrice,
            'is_pre_launch'  => true,
        ]);
    }
}