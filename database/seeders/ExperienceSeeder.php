<?php

namespace Database\Seeders;

use App\Domain\Experience\Enums\ExperienceStatus;
use App\Domain\Experience\Enums\ExperienceType;
use App\Domain\Experience\Models\Experience;
use App\Domain\User\Enums\UserRole;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = User::where('role', UserRole::PROVIDER)->get();

        if ($providers->isEmpty()) {
            $this->command->warn('⚠️  Aucun provider trouvé. Veuillez d\'abord exécuter UserSeeder.');
            return;
        }

        $experiences = [
            [
                'title' => 'Visite guidée de l\'île de Gorée',
                'description' => 'Découvrez l\'histoire fascinante de l\'île de Gorée, site classé au patrimoine mondial de l\'UNESCO. Visite guidée de la Maison des Esclaves, des musées et des ruelles historiques.',
                'short_description' => 'Visite historique de l\'île de Gorée',
                'type' => ExperienceType::TOUR,
                'status' => ExperienceStatus::APPROVED,
                'price' => 15000,
                'currency' => 'XOF',
                'duration_minutes' => 180,
                'max_participants' => 20,
                'min_participants' => 2,
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/gorée1.jpg',
                    'https://res.cloudinary.com/example/image/upload/v1/gorée2.jpg',
                ],
                'location' => [
                    'address' => 'Île de Gorée',
                    'city' => 'Dakar',
                    'region' => 'Dakar',
                    'coordinates' => [
                        'lat' => 14.6700,
                        'lng' => -17.3981,
                    ],
                ],
                'tags' => ['histoire', 'patrimoine', 'unesco', 'culture'],
                'amenities' => ['guide', 'transport', 'entrée musée'],
                'is_featured' => true,
                'published_at' => now(),
                'approved_at' => now(),
            ],
            [
                'title' => 'Atelier de cuisine sénégalaise',
                'description' => 'Apprenez à préparer le thiéboudienne, le plat national du Sénégal, avec un chef local. Découvrez les secrets de la cuisine sénégalaise et dégustez votre préparation.',
                'short_description' => 'Cuisinez le thiéboudienne avec un chef',
                'type' => ExperienceType::WORKSHOP,
                'status' => ExperienceStatus::PENDING,
                'price' => 25000,
                'currency' => 'XOF',
                'duration_minutes' => 240,
                'max_participants' => 8,
                'min_participants' => 2,
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/cuisine1.jpg',
                ],
                'location' => [
                    'address' => 'Quartier Almadies',
                    'city' => 'Dakar',
                    'region' => 'Dakar',
                    'coordinates' => [
                        'lat' => 14.7167,
                        'lng' => -17.4667,
                    ],
                ],
                'tags' => ['cuisine', 'culture', 'gastronomie'],
                'amenities' => ['matériel', 'ingrédients', 'repas inclus'],
                'is_featured' => false,
            ],
            [
                'title' => 'Safari dans le Parc National du Niokolo-Koba',
                'description' => 'Explorez la faune et la flore du plus grand parc national du Sénégal. Observation d\'éléphants, lions, buffles et de nombreuses espèces d\'oiseaux.',
                'short_description' => 'Safari dans le parc national',
                'type' => ExperienceType::ACTIVITY,
                'status' => ExperienceStatus::APPROVED,
                'price' => 50000,
                'currency' => 'XOF',
                'duration_minutes' => 480,
                'max_participants' => 15,
                'min_participants' => 4,
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/safari1.jpg',
                    'https://res.cloudinary.com/example/image/upload/v1/safari2.jpg',
                ],
                'location' => [
                    'address' => 'Parc National du Niokolo-Koba',
                    'city' => 'Tambacounda',
                    'region' => 'Tambacounda',
                    'coordinates' => [
                        'lat' => 13.0833,
                        'lng' => -13.3167,
                    ],
                ],
                'tags' => ['nature', 'safari', 'faune', 'flore'],
                'amenities' => ['transport', 'guide', 'déjeuner'],
                'is_featured' => true,
                'published_at' => now(),
                'approved_at' => now(),
            ],
            [
                'title' => 'Festival de musique traditionnelle',
                'description' => 'Assistez à un festival de musique traditionnelle sénégalaise avec des artistes locaux. Découvrez les rythmes du sabar, du mbalax et d\'autres styles musicaux.',
                'short_description' => 'Festival de musique traditionnelle',
                'type' => ExperienceType::EVENT,
                'status' => ExperienceStatus::REJECTED,
                'price' => 10000,
                'currency' => 'XOF',
                'duration_minutes' => 300,
                'max_participants' => 100,
                'min_participants' => 1,
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/musique1.jpg',
                ],
                'location' => [
                    'address' => 'Place de l\'Indépendance',
                    'city' => 'Dakar',
                    'region' => 'Dakar',
                    'coordinates' => [
                        'lat' => 14.6928,
                        'lng' => -17.4467,
                    ],
                ],
                'tags' => ['musique', 'culture', 'festival'],
                'amenities' => ['scène', 'sonorisation'],
                'is_featured' => false,
                'rejection_reason' => 'Informations incomplètes',
                'rejected_at' => now()->subDays(2),
            ],
            [
                'title' => 'Hébergement chez l\'habitant à Saint-Louis',
                'description' => 'Séjournez dans une maison traditionnelle à Saint-Louis, ville historique classée au patrimoine mondial. Immersion culturelle avec une famille locale.',
                'short_description' => 'Hébergement authentique à Saint-Louis',
                'type' => ExperienceType::ACCOMMODATION,
                'status' => ExperienceStatus::REPORTED,
                'price' => 20000,
                'currency' => 'XOF',
                'duration_minutes' => null,
                'max_participants' => 4,
                'min_participants' => 1,
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/hebergement1.jpg',
                ],
                'location' => [
                    'address' => 'Île de Saint-Louis',
                    'city' => 'Saint-Louis',
                    'region' => 'Saint-Louis',
                    'coordinates' => [
                        'lat' => 16.0333,
                        'lng' => -16.5000,
                    ],
                ],
                'tags' => ['hébergement', 'culture', 'immersion'],
                'amenities' => ['petit-déjeuner', 'wifi', 'chambre privée'],
                'is_featured' => false,
            ],
            [
                'title' => 'Restaurant gastronomique au bord de la mer',
                'description' => 'Dégustez une cuisine fusion sénégalo-française dans un restaurant avec vue sur l\'océan. Menu dégustation avec produits locaux.',
                'short_description' => 'Restaurant gastronomique avec vue mer',
                'type' => ExperienceType::RESTAURANT,
                'status' => ExperienceStatus::DRAFT,
                'price' => 35000,
                'currency' => 'XOF',
                'duration_minutes' => 120,
                'max_participants' => 30,
                'min_participants' => 1,
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/restaurant1.jpg',
                ],
                'location' => [
                    'address' => 'Corniche Ouest',
                    'city' => 'Dakar',
                    'region' => 'Dakar',
                    'coordinates' => [
                        'lat' => 14.7167,
                        'lng' => -17.4667,
                    ],
                ],
                'tags' => ['gastronomie', 'vue mer', 'romantique'],
                'amenities' => ['terrasse', 'parking', 'wifi'],
                'is_featured' => false,
            ],
        ];

        $createdCount = 0;
        foreach ($experiences as $index => $experienceData) {
            $provider = $providers->get($index % $providers->count());
            
            Experience::create(array_merge($experienceData, [
                'provider_id' => $provider->id,
            ]));
            
            $createdCount++;
        }

        $this->command->info("✅ {$createdCount} expériences créées avec succès !");
        $this->command->info('   - Statuts variés : approved, pending, rejected, reported, draft');
    }
}
