<?php


namespace App\Exports;

use App\Models\Reading;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReadingsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Reading::with('meter.user')->get()->map(function ($reading) {
            return [
                'id' => $reading->id,
                'meter_id' => $reading->meter_id,
                'user_name' => $reading->meter->user->name ?? 'N/A',
                'value' => $reading->value,
                'date' => $reading->date,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Meter ID',
            'User Name',
            'Consumption Value',
            'Date',
        ];
    }
}