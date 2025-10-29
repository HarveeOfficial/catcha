<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesPhilippinesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        // Curated list of commonly caught / key Philippine marine fish species (not exhaustive of all biodiversity)
        // Focus: commercial and artisanal fisheries targets.
        $data = [
            // common_name (Tagalog-English format), category
            ['Karpa-Carp (All kinds)', 'Fish'],
            ['Hito-Catfish', 'Fish'],
            ['Martiniko / Ar-aru-Climbing Perch', 'Fish'],
            ['Tuel-Croaker', 'Fish'],
            ['Kanduli-Freshwater catfish', 'Fish'],
            ['Igat / Palos-Freshwater eel', 'Fish'],
            ['Biya-Freshwater goby', 'Fish'],
            ['Gurami-Gourami', 'Fish'],
            ['Bangus-Milkfish', 'Fish'],
            ['Dalag-Mudfish', 'Fish'],
            ['Kapak / Purong-Mullet', 'Fish'],
            ['Ayungin-Silver perch', 'Fish'],
            ['Kitang-Spade fish', 'Fish'],
            ['Tilapya-Tilapia', 'Fish'],
            ['Bulan-bulan-Tarpoon', 'Fish'],
            ['Palos-Rice eel', 'Fish'],
            ['Bagsang-Bagsang', 'Fish'],
            ['Tulwan / Apahap-Lates calcarifer (Sea bass)', 'Fish'],
            ['Aramang-N. tenuipes', 'Crustaceans'],
            ['Dilis / Munamon-Anchovies', 'Fish'],
            ['Kurilaw-Arius spp.', 'Fish'],
            ['Angrat-Angrat', 'Fish'],
            ['Alimasag / Dariway-Blue crab', 'Crustaceans'],
            ['Dalagang-bukid-Caesio', 'Fish'],
            ['Talakitok-Cavalla / Trevally / Pompano', 'Fish'],
            ['Salay-salay-Crevalle', 'Fish'],
            ['Tuel / Simu / Girgiran-Croaker', 'Fish'],
            ['Pantranco-Dolphinfishes', 'Fish'],
            ['Kabasi-Gizzard shads', 'Fish'],
            ['Balaki-Goatfish / Fusilier', 'Fish'],
            ['Biya-Goby', 'Fish'],
            ['Lapu-lapu / Kurapu-Grouper', 'Fish'],
            ['Tagik-guk / Rodeko / Sidingan-Grunt', 'Fish'],
            ['Bulong-unas-Hairtails', 'Fish'],
            ['Siriw / Susay-Halfbeaks', 'Fish'],
            ['Biala-Kammal Thryssa', 'Fish'],
            ['Bidbid-Ladyfish / Tenpounder', 'Fish'],
            ['Al-alibut-Lizardfish', 'Fish'],
            ['Kabalyas-Mackerel (Short)', 'Fish'],
            ['Tangigi-Mackerel (Spanish / Indo-Pacific)', 'Fish'],
            ['Mataan-Mackerel (Blue / Chub)', 'Fish'],
            ['Malakapas-Mojarra / Silver Biddy', 'Fish'],
            ['Cadis-Moonfish', 'Fish'],
            ['Hagmang / Kiwo na Bevay-Morays', 'Fish'],
            ['Kugaw-Moustached thryssa', 'Fish'],
            ['Lul-luran / Purong-Mullet', 'Fish'],
            ['Mul-mul-Parrotfishes', 'Fish'],
            ['Kuraratu-Pike Congers', 'Fish'],
            ['Pargo / Bakulaw-Porgies', 'Fish'],
            ['Aber-Sardines and Herring (Aber)', 'Fish'],
            ['Bilis-Sardines and Herring (Bilis)', 'Fish'],
            ['Lao-lao-Sardines and Herring (Lao-lao)', 'Fish'],
            ['Tamban-Sardines and Herring (Tamban)', 'Fish'],
            ['Borador-Flyingfish', 'Fish'],
            ['Ipon-Goby Fry', 'Fish'],
            ['Inu-nu-Acetes', 'Crustaceans'],
            ['Suahe-Endeavor prawn', 'Crustaceans'],
            ['Talangka / Kappi-Freshwater crab', 'Crustaceans'],
            ['Ulang-Freshwater prawn', 'Crustaceans'],
            ['Hipon-Freshwater shrimp', 'Crustaceans'],
            ['Alimango-Mudcrab', 'Crustaceans'],
            ['Sugpo-Prawn', 'Crustaceans'],
            ['Hipong puti-White Shrimp', 'Crustaceans'],
            ['Mga batang alimango / Talangkang bata-Crablets / Crablings', 'Crustaceans'],
            ['Gamarung-Gamarung', 'Crustaceans'],
            ['Kabibi-Freshwater clams (Kabibi)', 'Molluscs'],
            ['Tulya-Freshwater clams (Tulya)', 'Molluscs'],
            ['Talaba-Oyster', 'Molluscs'],
            ['Kuhol-Shell', 'Molluscs'],
            ['Suso-Snail', 'Molluscs'],
            ['Binnek-Binnek', 'Molluscs'],
            ['Kaggu-Kaggu', 'Molluscs'],
            ['Liddeg-Liddeg', 'Molluscs'],
        ];

        $rows = [];
        foreach ($data as [$combined_name, $category]) {
            $rows[] = [
                'common_name' => $combined_name,
                'category' => $category,
                'seasonal_restrictions' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Delete existing species (respects foreign keys)
        Species::query()->delete();

        // Insert new species
        foreach ($rows as $row) {
            Species::create($row);
        }
    }
}
