<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Meter;
use App\Models\MonthlyConsumption;
use App\Models\Quartier;
use App\Models\Reading;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $casablanca = City::firstOrCreate(['name' => 'Casablanca']);
        $rabat = City::firstOrCreate(['name' => 'Rabat']);
        $fes = City::firstOrCreate(['name' => 'Fes']);

        $anfa = Quartier::firstOrCreate(['city_id' => $casablanca->id, 'name' => 'Anfa']);
        $maarif = Quartier::firstOrCreate(['city_id' => $casablanca->id, 'name' => 'Maarif']);
        $agdal = Quartier::firstOrCreate(['city_id' => $rabat->id, 'name' => 'Agdal']);
        $hassan = Quartier::firstOrCreate(['city_id' => $rabat->id, 'name' => 'Hassan']);
        $villeNouvelleFes = Quartier::firstOrCreate(['city_id' => $fes->id, 'name' => 'Ville Nouvelle']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@energy.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'city_id' => $casablanca->id,
                'quartier' => $anfa->name,
                'house_number' => '100',
                'phone' => '0600000001',
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']);

        $citizen1 = User::updateOrCreate(
            ['email' => 'amina@energy.test'],
            [
                'name' => 'Amina Benali',
                'password' => Hash::make('password123'),
                'city_id' => $casablanca->id,
                'quartier' => $maarif->name,
                'house_number' => '12',
                'phone' => '0600000002',
                'is_active' => true,
            ]
        );
        $citizen1->syncRoles(['citoyen']);

        $citizen2 = User::updateOrCreate(
            ['email' => 'youssef@energy.test'],
            [
                'name' => 'Youssef Alami',
                'password' => Hash::make('password123'),
                'city_id' => $rabat->id,
                'quartier' => $agdal->name,
                'house_number' => '8',
                'phone' => '0600000003',
                'is_active' => true,
            ]
        );
        $citizen2->syncRoles(['citoyen']);

        $citizen3 = User::updateOrCreate(
            ['email' => 'salma@energy.test'],
            [
                'name' => 'Salma Idrissi',
                'password' => Hash::make('password123'),
                'city_id' => $fes->id,
                'quartier' => $villeNouvelleFes->name,
                'house_number' => '24',
                'phone' => '0600000004',
                'is_active' => true,
            ]
        );
        $citizen3->syncRoles(['citoyen']);

        $this->seedMetersAndConsumptions($citizen1, $maarif, 1100);
        $this->seedMetersAndConsumptions($citizen2, $hassan, 900);
        $this->seedMetersAndConsumptions($citizen3, $villeNouvelleFes, 980);
    }

    private function seedMetersAndConsumptions(User $user, Quartier $quartier, int $base): void
    {
        $meters = [
            [
                'name' => "{$user->name} Electricity Meter",
                'type' => 'electricity',
                'unit' => 'kWh',
                'location' => 'living room',
                'reading_step' => 14,
                'monthly_step' => 45,
            ],
            [
                'name' => "{$user->name} Water Meter",
                'type' => 'water',
                'unit' => 'liters',
                'location' => 'kitchen',
                'reading_step' => 70,
                'monthly_step' => 210,
            ],
            [
                'name' => "{$user->name} Gas Meter",
                'type' => 'gas',
                'unit' => 'm³',
                'location' => 'utility room',
                'reading_step' => 6,
                'monthly_step' => 20,
            ],
        ];

        foreach ($meters as $index => $meterData) {
            $meter = Meter::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $meterData['name'],
                ],
                [
                    'quartier_id' => $quartier->id,
                    'type' => $meterData['type'],
                    'unit' => $meterData['unit'],
                    'location' => $meterData['location'],
                ]
            );

            $this->seedReadings($meter, $base + ($index * 100), $meterData['reading_step']);
            $this->seedMonthlyConsumptions($meter, 6, $base + ($index * 120), $meterData['monthly_step']);
        }
    }

    private function seedReadings(Meter $meter, float $start, float $step): void
    {
        $today = Carbon::today();

        for ($i = 9; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $value = $start + ((9 - $i) * $step);

            Reading::updateOrCreate(
                [
                    'meter_id' => $meter->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }

    private function seedMonthlyConsumptions(Meter $meter, int $months, float $start, float $step): void
    {
        $currentMonth = Carbon::now()->startOfMonth();

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = $currentMonth->copy()->subMonths($i);
            $value = $start + (($months - 1 - $i) * $step);

            MonthlyConsumption::updateOrCreate(
                [
                    'meter_id' => $meter->id,
                    'month' => $month->toDateString(),
                ],
                [
                    'consumption_value' => $value,
                ]
            );
        }
    }
}
