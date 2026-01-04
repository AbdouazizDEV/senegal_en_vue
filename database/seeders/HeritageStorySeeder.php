<?php

namespace Database\Seeders;

use App\Domain\Content\Enums\ContentStatus;
use App\Domain\Content\Models\HeritageStory;
use App\Domain\User\Enums\UserRole;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HeritageStorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', UserRole::ADMIN)->first();

        if (!$admin) {
            $this->command->warn('⚠️  Aucun admin trouvé. Veuillez d\'abord exécuter UserSeeder.');
            return;
        }

        $stories = [
            [
                'title' => 'L\'histoire de l\'île de Gorée',
                'content' => 'L\'île de Gorée, située à 3 km au large de Dakar, est un témoin silencieux de l\'histoire douloureuse de la traite des esclaves. Classée au patrimoine mondial de l\'UNESCO en 1978, cette petite île de 28 hectares raconte l\'histoire de millions d\'Africains déportés vers les Amériques. La Maison des Esclaves, construite en 1776, est aujourd\'hui un mémorial qui rappelle cette tragédie humaine.',
                'excerpt' => 'Découvrez l\'histoire de l\'île de Gorée, symbole de la mémoire de la traite des esclaves.',
                'author_name' => 'Amadou Ba',
                'author_location' => 'Dakar, Sénégal',
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/heritage/gorée1.jpg',
                    'https://res.cloudinary.com/example/image/upload/v1/heritage/gorée2.jpg',
                    'https://res.cloudinary.com/example/image/upload/v1/heritage/gorée3.jpg',
                ],
                'tags' => ['histoire', 'patrimoine', 'unesco', 'mémoire'],
                'status' => ContentStatus::PUBLISHED,
                'is_featured' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Les traditions du peuple Wolof',
                'content' => 'Le peuple Wolof, majoritaire au Sénégal, possède une riche tradition orale transmise de génération en génération. Les griots, gardiens de la mémoire collective, racontent l\'histoire des familles et des royaumes. Les cérémonies traditionnelles comme le baptême, le mariage et les funérailles sont des moments importants de la vie sociale où se perpétuent les coutumes ancestrales.',
                'excerpt' => 'Explorez les traditions et la culture du peuple Wolof, gardien de la mémoire sénégalaise.',
                'author_name' => 'Fatou Diop',
                'author_location' => 'Thiès, Sénégal',
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/heritage/wolof1.jpg',
                    'https://res.cloudinary.com/example/image/upload/v1/heritage/wolof2.jpg',
                ],
                'tags' => ['culture', 'tradition', 'wolof', 'griots'],
                'status' => ContentStatus::PUBLISHED,
                'is_featured' => false,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'L\'architecture coloniale de Saint-Louis',
                'content' => 'Saint-Louis, première capitale de l\'Afrique occidentale française, conserve un patrimoine architectural exceptionnel. Les maisons coloniales aux balcons en fer forgé, les bâtiments administratifs et les places publiques témoignent de l\'histoire de cette ville fondée en 1659. Classée au patrimoine mondial de l\'UNESCO, Saint-Louis est un musée à ciel ouvert.',
                'excerpt' => 'Découvrez l\'architecture coloniale unique de Saint-Louis, ville historique du Sénégal.',
                'author_name' => 'Ibrahima Ndiaye',
                'author_location' => 'Saint-Louis, Sénégal',
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/heritage/saintlouis1.jpg',
                    'https://res.cloudinary.com/example/image/upload/v1/heritage/saintlouis2.jpg',
                ],
                'tags' => ['architecture', 'patrimoine', 'saint-louis', 'histoire'],
                'status' => ContentStatus::PUBLISHED,
                'is_featured' => true,
                'published_at' => now()->subDays(2),
            ],
        ];

        foreach ($stories as $storyData) {
            HeritageStory::firstOrCreate(
                ['slug' => Str::slug($storyData['title'])],
                array_merge($storyData, [
                    'created_by' => $admin->id,
                    'uuid' => (string) Str::uuid(),
                ])
            );
        }

        $this->command->info('✅ ' . count($stories) . ' histoires du patrimoine créées avec succès !');
    }
}
