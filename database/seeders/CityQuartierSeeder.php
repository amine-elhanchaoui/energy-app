<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Quartier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CityQuartierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $citiesWithQuartiers = [
            'Casablanca' => [
                'Medina',
                'Nouvelle Medina',
                'Anfa',
                'Gauthier',
                'Ain Diab',
                'Ben M\'Sick',
                'Derb Sultan',
                'Sidi Moumen',
                'Fez',
                'Mers Sultan'
            ],
            'Fes' => [
                'Medina',
                'Ville Nouvelle',
                'Dhar Mehrez',
                'Ziat',
                'Saïs',
                'Jdida',
                'Dhar el Marsa',
                'El Kaat'
            ],
            'Sale' => [
                'Medina',
                'Ville Nouvelle',
                'Takaddoum',
                'Hay Hassani',
                'Sidi Mukhtar',
                'Karyan',
                'Hay El Firdaous'
            ],
            'Marrakesh' => [
                'Medina',
                'Gueliz',
                'Hivernage',
                'Sidi Youssef',
                'Kasbah',
                'Ben Guerir',
                'Essaouira Road',
                'Oasis'
            ],
            'Tangier' => [
                'Medina',
                'Ville Nouvelle',
                'Hay Ennasr',
                'Hay Sghir',
                'Charf',
                'Mghogha',
                'Boubana',
                'Riad'
            ],
            'Rabat' => [
                'Medina',
                'Ville Nouvelle',
                'Agdal',
                'Hassan',
                'Hay Riad',
                'Souissi',
                'Hay El Firdaous',
                'Takaddoum'
            ],
            'Meknes' => [
                'Medina',
                'Ville Nouvelle',
                'Jamaâ',
                'Sidi Kacem',
                'Zerhoune',
                'Moulay Ismail',
                'Akkad'
            ],
            'Oujda' => [
                'Medina',
                'Ville Nouvelle',
                'Hay Hassani',
                'Sidi Aissa',
                'Casbah',
                'Bachiri'
            ],
            'Kenitra' => [
                'Medina',
                'Ville Nouvelle',
                'Karamat',
                'Sidi Boukhnadal',
                'Doukkala'
            ],
            'Agadir' => [
                'Medina',
                'Ville Nouvelle',
                'Talborjt',
                'Bahia',
                'Marshan',
                'Drarga',
                'Anza'
            ],
            'Tetouan' => [
                'Medina',
                'Ville Nouvelle',
                'Santiago',
                'Sidi Mandri',
                'Hay el Farabi'
            ],
            'Safi' => [
                'Medina',
                'Ville Nouvelle',
                'Jdida',
                'Sidi Aissa'
            ],
            'Temara' => [
                'Centre',
                'Skhirat',
                'Akrach',
                'Harhoura'
            ],
            'Inzegan' => [
                'Centre',
                'Hay Salam',
                'Hay Nour'
            ],
            'Mohammedia' => [
                'Centre',
                'Hay El Firdaous',
                'Ain Harrouda',
                'Plage'
            ],
            'Laayoune' => [
                'Centre',
                'Hay Hassani',
                'Hay El Baraka',
                'Hay Sawira'
            ],
            'Khouribga' => [
                'Centre',
                'Hay Slamane',
                'Hay El Kasbah'
            ],
            'Beni Mellal' => [
                'Centre',
                'Hay Slamane',
                'Kasbah',
                'Binebrine'
            ],
            'Jdida' => [
                'Centre',
                'Hay Hassani',
                'Kasbah'
            ],
            'Taza' => [
                'Medina',
                'Ville Nouvelle',
                'Sidi Boukhnadel'
            ],
            'Ait Melloul' => [
                'Centre',
                'Hay Nour',
                'Hay Salam'
            ],
            'Nador' => [
                'Centre',
                'Hay Hassani',
                'Hay EL Emel'
            ],
            'Settat' => [
                'Centre',
                'Hay Slamane',
                'Kasbah'
            ],
            'Ksar El Kbir' => [
                'Centre',
                'Hay Hassani'
            ],
            'Larache' => [
                'Medina',
                'Ville Nouvelle',
                'Zankat Sidi Driss'
            ],
            'Khemisset' => [
                'Centre',
                'Hay Slamane'
            ],
            'Guelmim' => [
                'Centre',
                'Hay Hassani'
            ],
            'Berrechid' => [
                'Centre',
                'Hay El Kasbah'
            ],
            'Wad Zam' => [
                'Centre'
            ],
            'Fkih Ben Saleh' => [
                'Centre'
            ],
            'Taourirt' => [
                'Centre',
                'Hay Slamane'
            ],
            'Berkane' => [
                'Centre',
                'Hay Hassani'
            ],
            'Sidi Slimane' => [
                'Centre',
                'Hay Slamane'
            ],
            'Errachidia' => [
                'Centre',
                'Hay Slamane'
            ],
            'Sidi Kacem' => [
                'Centre'
            ],
            'Khenifra' => [
                'Centre',
                'Hay Slamane'
            ],
            'Tifelt' => [
                'Centre'
            ],
            'Essaouira' => [
                'Medina',
                'Ville Nouvelle',
                'Sidi Mokhtar'
            ],
            'Taroudant' => [
                'Medina',
                'Ville Nouvelle'
            ],
            'El Kelaa des Sraghna' => [
                'Centre'
            ],
            'Oulad Teima' => [
                'Centre'
            ],
            'Youssoufia' => [
                'Centre',
                'Hay Hassani'
            ],
            'Sefrou' => [
                'Medina',
                'Ville Nouvelle'
            ],
            'Ben Guerir' => [
                'Centre'
            ],
            'Tan-Tan' => [
                'Centre'
            ],
            'Ouazzane' => [
                'Centre'
            ],
            'Guercif' => [
                'Centre'
            ],
            'Dakhla' => [
                'Centre',
                'Hay Wakil'
            ],
            'Hoceima' => [
                'Centre',
                'Hay Hassani'
            ],
            'Fnideq' => [
                'Centre'
            ],
            'Ouarzazate' => [
                'Centre',
                'Hay Slamane'
            ],
            'Tiznit' => [
                'Centre',
                'Hay Hassani'
            ],
            'Suq Sebt Oulad Nama' => [
                'Centre'
            ],
            'Azrou' => [
                'Centre'
            ],
            'Lahraouyine' => [
                'Centre'
            ],
            'Ben Slimane' => [
                'Centre'
            ],
            'Midelt' => [
                'Centre'
            ],
            'Jerada' => [
                'Centre'
            ],
            'Skhirat' => [
                'Centre'
            ],
            'Souk Larbaa' => [
                'Centre'
            ],
            'Ain Harrouda' => [
                'Centre'
            ],
            'Boujad' => [
                'Centre'
            ],
            'Kasbat Tadla' => [
                'Centre'
            ],
            'Sidi Bennour' => [
                'Centre'
            ],
            'Martil' => [
                'Centre'
            ],
            'Lqliaa' => [
                'Centre'
            ],
            'Cape Bojador' => [
                'Centre'
            ],
            'Azemmour' => [
                'Medina',
                'Ville Nouvelle'
            ],
            'M\'diq' => [
                'Centre'
            ],
            'Tinghir' => [
                'Centre'
            ],
            'Al Aaroui' => [
                'Centre'
            ],
            'Chefchaouen' => [
                'Medina',
                'Ville Nouvelle'
            ],
            'M\'Rirt' => [
                'Centre'
            ],
            'Zagora' => [
                'Centre'
            ],
            'El Aioun Sidi Mellouk' => [
                'Centre'
            ],
            'Lamkansa' => [
                'Centre'
            ],
            'Smara' => [
                'Centre'
            ],
            'Taounate' => [
                'Centre'
            ],
            'Bin Anşār' => [
                'Centre'
            ],
            'Sidi Yahya El Gharb' => [
                'Centre'
            ],
            'Zaio' => [
                'Centre'
            ],
            'Amalou Ighriben' => [
                'Centre'
            ],
            'Asilah' => [
                'Medina',
                'Ville Nouvelle'
            ],
            'Azilal' => [
                'Centre'
            ],
            'Mechra Bel Ksiri' => [
                'Centre'
            ],
            'El Hajeb' => [
                'Centre'
            ],
            'Bouznika' => [
                'Centre'
            ],
            'Imzouren' => [
                'Centre'
            ],
            'Tahla' => [
                'Centre'
            ],
            'BouiZazarene Ihaddadene' => [
                'Centre'
            ],
            'Ain El Aouda' => [
                'Centre'
            ],
            'Bouarfa' => [
                'Centre'
            ],
            'Arfoud' => [
                'Centre'
            ],
            'Demnate' => [
                'Centre'
            ],
            'Sidi Slimane Echcharraa' => [
                'Centre'
            ],
            'Zaouiat Cheikh' => [
                'Centre'
            ],
            'Ain Taoujdate' => [
                'Centre'
            ],
            'Echemmaia' => [
                'Centre'
            ]
        ];

        // Loop through all cities and create quartiers
        foreach ($citiesWithQuartiers as $cityName => $quartiers) {
            // Find or create the city
            $city = City::firstOrCreate(['name' => $cityName]);

            // Create quartiers for this city
            foreach ($quartiers as $quartierName) {
                Quartier::firstOrCreate(
                    [
                        'city_id' => $city->id,
                        'name' => $quartierName
                    ]
                );
            }
        }
    }
}
