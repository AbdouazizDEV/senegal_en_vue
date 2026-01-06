<?php

namespace App\Application\Traveler\Message\Handlers;

use App\Application\Traveler\Message\Commands\ContactProviderCommand;
use App\Domain\Message\Models\Conversation;
use App\Domain\Message\Models\Message;
use App\Infrastructure\Repositories\Contracts\ConversationRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\MessageRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class ContactProviderHandler
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private MessageRepositoryInterface $messageRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(ContactProviderCommand $command): Conversation
    {
        // Vérifier que le prestataire existe et a le bon rôle
        $provider = $this->userRepository->findById($command->providerId);
        if (!$provider || $provider->role->value !== 'provider') {
            throw new \Exception('Prestataire non trouvé ou invalide.');
        }

        // Chercher ou créer la conversation
        $conversation = $this->conversationRepository->findByTravelerAndProvider(
            $command->travelerId,
            $command->providerId,
            $command->experienceId
        );

        if (!$conversation) {
            $conversation = $this->conversationRepository->create([
                'traveler_id' => $command->travelerId,
                'provider_id' => $command->providerId,
                'experience_id' => $command->experienceId,
                'booking_id' => $command->bookingId,
                'subject' => $command->subject,
                'status' => 'active',
                'last_message_at' => now(),
                'unread_count_provider' => 1,
            ]);
        } else {
            // Mettre à jour la conversation
            $this->conversationRepository->update($conversation, [
                'last_message_at' => now(),
                'unread_count_provider' => $conversation->unread_count_provider + 1,
            ]);
        }

        // Créer le message
        $this->messageRepository->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $command->travelerId,
            'receiver_id' => $command->providerId,
            'content' => $command->message,
            'type' => $command->attachments ? 'image' : 'text',
            'attachments' => $command->attachments,
        ]);

        return $conversation->fresh(['traveler', 'provider', 'experience', 'booking']);
    }
}


