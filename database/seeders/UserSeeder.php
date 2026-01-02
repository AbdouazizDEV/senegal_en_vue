<?php

namespace Database\Seeders;

use App\Domain\User\Enums\UserRole;
use App\Domain\User\Enums\UserStatus;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@senegalenvue.com'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('password123'),
                'role' => UserRole::ADMIN,
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // Providers
        $providers = [
            [
                'name' => 'Amadou Diallo',
                'email' => 'amadou.provider@example.com',
                'phone' => '+221771234567',
                'role' => UserRole::PROVIDER,
                'status' => UserStatus::VERIFIED,
                'bio' => 'Guide touristique expérimenté à Dakar',
            ],
            [
                'name' => 'Fatou Sarr',
                'email' => 'fatou.provider@example.com',
                'phone' => '+221771234568',
                'role' => UserRole::PROVIDER,
                'status' => UserStatus::PENDING_VERIFICATION,
                'bio' => 'Organisatrice d\'événements culturels',
            ],
            [
                'name' => 'Ibrahima Ndiaye',
                'email' => 'ibrahima.provider@example.com',
                'phone' => '+221771234569',
                'role' => UserRole::PROVIDER,
                'status' => UserStatus::VERIFIED,
                'bio' => 'Chef cuisinier spécialisé en cuisine sénégalaise',
            ],
        ];

        foreach ($providers as $provider) {
            if (!User::where('email', $provider['email'])->exists()) {
                User::create(array_merge($provider, [
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]));
            }
        }

        // Travelers
        $travelers = [
            [
                'name' => 'Marie Dupont',
                'email' => 'marie.traveler@example.com',
                'phone' => '+221771234570',
                'role' => UserRole::TRAVELER,
                'status' => UserStatus::ACTIVE,
            ],
            [
                'name' => 'Jean Martin',
                'email' => 'jean.traveler@example.com',
                'phone' => '+221771234571',
                'role' => UserRole::TRAVELER,
                'status' => UserStatus::ACTIVE,
            ],
            [
                'name' => 'Sophie Bernard',
                'email' => 'sophie.traveler@example.com',
                'phone' => '+221771234572',
                'role' => UserRole::TRAVELER,
                'status' => UserStatus::ACTIVE,
            ],
        ];

        foreach ($travelers as $traveler) {
            if (!User::where('email', $traveler['email'])->exists()) {
                User::create(array_merge($traveler, [
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]));
            }
        }

        $this->command->info('✅ Users créés avec succès !');
        $this->command->info('   - 1 Admin');
        $this->command->info('   - ' . count($providers) . ' Providers');
        $this->command->info('   - ' . count($travelers) . ' Travelers');
    }
}
