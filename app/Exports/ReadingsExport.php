<?php


namespace App\Exports;

use App\Models\Reading;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReadingsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Reading::with(['meter.user.city', 'meter.quartier'])
            ->latest('date')
            ->get();
    }

    public function map($reading): array
    {
        return [
            $reading->id,
            $reading->meter_id,
            $reading->meter?->user?->name ?? 'N/A',
            $reading->meter?->user?->email ?? 'N/A',
            ucfirst($reading->meter?->type ?? 'N/A'),
            $reading->meter?->unit ?? '',
            $reading->meter?->quartier?->name ?? 'N/A',
            $reading->meter?->user?->city?->name ?? 'N/A',
            number_format((float) $reading->value, 2, '.', ''),
            Carbon::parse($reading->date)->format('Y-m-d'),
            optional($reading->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Meter ID',
            'User Name',
            'User Email',
            'Meter Type',
            'Unit',
            'Quartier',
            'City',
            'Consumption Value',
            'Reading Date',
            'Created At',
        ];
    }
}