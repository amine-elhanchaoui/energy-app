<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use Illuminate\Support\Facades\File;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = storage_path('app/public/list-cities-morocco-98j.csv');
        if (!File::exists($csvPath)) {
            $this->command->error('CSV file not found at ' . $csvPath);
            return;
        }

        $file = fopen($csvPath, 'r');
        fgetcsv($file); // Skip header

        $cities = [];
        while (($row = fgetcsv($file)) !== false) {
            if (isset($row[1]) && trim($row[1]) !== '') {
                $cityName = trim($row[1]);
                if (!City::where('name', $cityName)->exists()) {
                    $cities[] = ['name' => $cityName, 'created_at' => now(), 'updated_at' => now()];
                }
            }
        }
        fclose($file);

        if (!empty($cities)) {
            City::insert($cities);
            $this->command->info('Seeded ' . count($cities) . ' cities from CSV.');
        } else {
            $this->command->info('No new cities to seed.');
        }
    }
}
