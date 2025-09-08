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
            // common, filipino, scientific, status, min_size_cm
            ['Yellowfin Tuna', 'Tambakol / Tulingan', 'Thunnus albacares', 'LC', 40],
            ['Skipjack Tuna', 'Gulyasan / Tulingan', 'Katsuwonus pelamis', 'LC', 35],
            ['Bigeye Tuna', 'Tambakol (Bigeye)', 'Thunnus obesus', 'VU', 45],
            ['Frigate Tuna', 'Tulingan', 'Auxis thazard', 'LC', 25],
            ['Eastern Little Tuna (Kawakawa)', 'Kawakawa', 'Euthynnus affinis', 'LC', 30],
            ['Mahi-Mahi', 'Dorado', 'Coryphaena hippurus', 'LC', 50],
            ['Blue Marlin', 'Maliputo (misapplied) / Blue Marlin', 'Makaira nigricans', 'VU', 150],
            ['Black Marlin', 'Black Marlin', 'Istiompax indica', null, 180],
            ['Sailfish', 'Istiophorus / Layag-layag', 'Istiophorus platypterus', 'LC', 140],
            ['Wahoo', 'Tangigue', 'Acanthocybium solandri', 'LC', 75],
            ['Narrow-barred Spanish Mackerel', 'Tanigue (Spanish)', 'Scomberomorus commerson', 'NT', 55],
            ['Indo-Pacific King Mackerel', 'Tanigue (King)', 'Scomberomorus guttatus', 'NT', 45],
            ['Short-bodied Mackerel', 'Alumahan', 'Rastrelliger brachysoma', 'LC', 15],
            ['Indian Mackerel', 'Alumahan / Anduhaw', 'Rastrelliger kanagurta', 'LC', 15],
            ['Round Scad (Galunggong)', 'Galunggong', 'Decapterus macrosoma', 'LC', 12],
            ['Yellowstripe Scad', 'Salay-salay', 'Selaroides leptolepis', 'LC', 10],
            ['Bigeye Scad', 'Matangbaka', 'Selar crumenophthalmus', 'LC', 18],
            ['Threadfin Bream', 'Bisugo', 'Nemipterus japonicus', 'LC', 16],
            ['Goatfish (Red)', 'Tiup-tiup', 'Mulloidichthys vanicolensis', 'LC', 18],
            ['Goatfish (Yellowstripe)', 'Dilaw na Tiup-tiup', 'Upeneus sulphureus', 'LC', 16],
            ['Rabbitfish (Siganid)', 'Danggit / Samaral', 'Siganus canaliculatus', 'LC', 14],
            ['Milkfish', 'Bangus', 'Chanos chanos', 'LC', 25],
            ['Grouper (Leopard Coral)', 'Sunong / Lapulapu', 'Plectropomus leopardus', 'VU', 38],
            ['Grouper (Orange-spotted)', 'Sunong / Lapulapu', 'Epinephelus coioides', 'NT', 40],
            ['Grouper (Tiger)', 'Sunong / Lapulapu', 'Epinephelus fuscoguttatus', 'VU', 45],
            ['Red Snapper', 'Maya-maya', 'Lutjanus argentimaculatus', 'LC', 35],
            ['Mangrove Snapper', 'Mangrove Maya-maya', 'Lutjanus argentimaculatus', 'LC', 30],
            ['Yellowtail Fusilier', 'Dalagang-bukid (Fusilier)', 'Caesio cunning', 'LC', 18],
            ['Dalagang Bukid (Yellowtail Emperor)', 'Dalagang-bukid', 'Gnathodentex aureolineatus', 'LC', 22],
            ['Cavalla (Trevally)', 'Talakitok', 'Caranx ignobilis', 'NT', 60],
            ['Horse Mackerel (Jack)', 'Salay-salay (Jack)', 'Atule mate', 'LC', 18],
            ['Anchovy (Bolinao)', 'Dilis / Bolinao', 'Encrasicholina heteroloba', 'LC', 8],
            ['Silver Perch (Bidbid)', 'Bidbid', 'Gerres filamentosus', 'LC', 14],
            ['Emperor (Lison)', 'Lison', 'Lethrinus lentjan', 'LC', 25],
            ['Parrotfish (Scarid)', 'Mol Mol / Loro', 'Scarus ghobban', 'LC', 20],
            ['Spinefoot (Rabbitfish)', 'Samaral', 'Siganus fuscescens', 'LC', 14],
            ['Flying Fish', 'Lumod / Bolador', 'Cheilopogon cyanopterus', 'LC', 18],
            ['Shad (Gizzard)', 'Balila', 'Nematalosa nasus', 'LC', 20],
            ['Tiger Prawn (for reference)', 'Sugpo', 'Penaeus monodon', null, null],
            ['Blue Swimming Crab', 'Alimasag', 'Portunus pelagicus', 'V', null],
        ];

        $rows = [];
        foreach ($data as [$common,$filipino,$scientific,$status,$min]) {
            $rows[] = [
                'common_name' => $common,
                'filipino_name' => $filipino,
                'scientific_name' => $scientific,
                'conservation_status' => $status,
                'min_size_cm' => $min,
                'seasonal_restrictions' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Upsert: ensure Filipino names get filled for existing records
        foreach ($rows as $row) {
            $existing = Species::where('common_name', $row['common_name'])->first();
            if ($existing) {
                $update = [];
                if (empty($existing->filipino_name) && ! empty($row['filipino_name'])) {
                    $update['filipino_name'] = $row['filipino_name'];
                }
                if (empty($existing->scientific_name) && ! empty($row['scientific_name'])) {
                    $update['scientific_name'] = $row['scientific_name'];
                }
                if (! empty($row['conservation_status']) && $existing->conservation_status !== $row['conservation_status']) {
                    $update['conservation_status'] = $row['conservation_status'];
                }
                if (! is_null($row['min_size_cm']) && ($existing->min_size_cm === null || (float) $existing->min_size_cm === 0.0)) {
                    $update['min_size_cm'] = $row['min_size_cm'];
                }
                if (! empty($update)) {
                    $existing->update($update);
                }
            } else {
                Species::create($row);
            }
        }
    }
}
