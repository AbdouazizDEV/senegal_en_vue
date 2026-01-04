<?php

namespace Database\Seeders;

use App\Domain\Content\Enums\ContentStatus;
use App\Domain\Content\Models\BlogPost;
use App\Domain\User\Enums\UserRole;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
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

        $posts = [
            [
                'title' => '10 raisons de visiter le Sénégal en 2026',
                'content' => 'Le Sénégal, pays de la Teranga (hospitalité), offre une expérience de voyage unique en Afrique de l\'Ouest. De Dakar à Saint-Louis, en passant par les plages de Saly et les parcs nationaux, découvrez pourquoi le Sénégal devrait être votre prochaine destination. Cet article vous présente les 10 meilleures raisons de visiter ce magnifique pays.',
                'excerpt' => 'Découvrez pourquoi le Sénégal est la destination à ne pas manquer en 2026.',
                'featured_image' => 'https://res.cloudinary.com/example/image/upload/v1/blog/senegal-featured.jpg',
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/blog/senegal1.jpg',
                    'https://res.cloudinary.com/example/image/upload/v1/blog/senegal2.jpg',
                ],
                'tags' => ['tourisme', 'voyage', 'découverte', 'sénégal'],
                'status' => ContentStatus::PUBLISHED,
                'is_featured' => true,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Guide complet : Comment préparer votre voyage au Sénégal',
                'content' => 'Préparer un voyage au Sénégal nécessite quelques connaissances de base. Dans ce guide complet, nous vous expliquons tout ce que vous devez savoir : les formalités administratives, les meilleures périodes pour voyager, les vaccins recommandés, la monnaie locale, les moyens de transport, et bien plus encore.',
                'excerpt' => 'Tout ce que vous devez savoir pour bien préparer votre voyage au Sénégal.',
                'featured_image' => 'https://res.cloudinary.com/example/image/upload/v1/blog/guide-featured.jpg',
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/blog/guide1.jpg',
                ],
                'tags' => ['guide', 'pratique', 'conseils', 'voyage'],
                'status' => ContentStatus::PUBLISHED,
                'is_featured' => false,
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Les plats sénégalais à absolument goûter',
                'content' => 'La cuisine sénégalaise est réputée pour sa richesse et sa diversité. Du thiéboudienne au yassa en passant par le mafé et le ceebu jën, découvrez les plats emblématiques du Sénégal. Chaque région a ses spécialités culinaires qui racontent l\'histoire et la culture du pays.',
                'excerpt' => 'Découvrez les plats emblématiques de la cuisine sénégalaise.',
                'featured_image' => 'https://res.cloudinary.com/example/image/upload/v1/blog/cuisine-featured.jpg',
                'images' => [
                    'https://res.cloudinary.com/example/image/upload/v1/blog/cuisine1.jpg',
                    'https://res.cloudinary.com/example/image/upload/v1/blog/cuisine2.jpg',
                ],
                'tags' => ['cuisine', 'gastronomie', 'culture', 'découverte'],
                'status' => ContentStatus::PUBLISHED,
                'is_featured' => true,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($posts as $postData) {
            BlogPost::firstOrCreate(
                ['slug' => Str::slug($postData['title'])],
                array_merge($postData, [
                    'author_id' => $admin->id,
                    'uuid' => (string) Str::uuid(),
                ])
            );
        }

        $this->command->info('✅ ' . count($posts) . ' articles de blog créés avec succès !');
    }
}
