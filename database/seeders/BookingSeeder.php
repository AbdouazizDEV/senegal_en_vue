<?php

namespace Database\Seeders;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Enums\PaymentStatus;
use App\Domain\Booking\Models\Booking;
use App\Domain\Experience\Enums\ExperienceStatus;
use App\Domain\Experience\Models\Experience;
use App\Domain\User\Enums\UserRole;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $travelers = User::where('role', UserRole::TRAVELER)->get();
        $experiences = Experience::where('status', ExperienceStatus::APPROVED)->get();

        if ($travelers->isEmpty() || $experiences->isEmpty()) {
            $this->command->warn('⚠️  Voyageurs ou expériences manquants. Veuillez d\'abord exécuter UserSeeder et ExperienceSeeder.');
            return;
        }

        $bookings = [
            [
                'booking_date' => now()->addDays(7),
                'number_of_participants' => 2,
                'status' => BookingStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PAID,
                'notes' => 'Première visite au Sénégal, très enthousiastes !',
            ],
            [
                'booking_date' => now()->addDays(10),
                'number_of_participants' => 4,
                'status' => BookingStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'notes' => 'Groupe d\'amis en voyage',
            ],
            [
                'booking_date' => now()->addDays(15),
                'number_of_participants' => 1,
                'status' => BookingStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PAID,
                'notes' => null,
            ],
            [
                'booking_date' => now()->addDays(20),
                'number_of_participants' => 3,
                'status' => BookingStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'notes' => 'Expérience excellente !',
            ],
            [
                'booking_date' => now()->subDays(5),
                'number_of_participants' => 2,
                'status' => BookingStatus::CANCELLED,
                'payment_status' => PaymentStatus::REFUNDED,
                'notes' => 'Annulation pour raison personnelle',
            ],
        ];

        $createdCount = 0;
        foreach ($bookings as $bookingData) {
            $traveler = $travelers->random();
            $experience = $experiences->random();
            
            $participantsCount = $bookingData['number_of_participants'];
            $totalAmount = $experience->price * $participantsCount;
            
            Booking::create([
                'traveler_id' => $traveler->id,
                'experience_id' => $experience->id,
                'provider_id' => $experience->provider_id,
                'booking_date' => $bookingData['booking_date'],
                'participants_count' => $participantsCount,
                'total_amount' => $totalAmount,
                'currency' => $experience->currency,
                'status' => $bookingData['status'],
                'payment_status' => $bookingData['payment_status'],
                'special_requests' => $bookingData['notes'],
                'uuid' => (string) Str::uuid(),
            ]);
            
            $createdCount++;
        }

        $this->command->info("✅ {$createdCount} réservations créées avec succès !");
        $this->command->info('   - Statuts variés : confirmed, pending, completed, cancelled');
    }
}
