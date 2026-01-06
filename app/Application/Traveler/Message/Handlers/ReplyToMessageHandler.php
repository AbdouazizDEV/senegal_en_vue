<?php

namespace App\Application\Traveler\Message\Handlers;

use App\Application\Traveler\Message\Commands\ReplyToMessageCommand;
use App\Domain\Message\Models\Conversation;
use App\Infrastructure\Repositories\Contracts\ConversationRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\MessageRepositoryInterface;

class ReplyToMessageHandler
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private MessageRepositoryInterface $messageRepository
    ) {}

    public function handle(ReplyToMessageCommand $command): Conversation
    {
        $conversation = $this->conversationRepository->findById($command->conversationId);

        if (!$conversation || $conversation->traveler_id !== $command->travelerId) {
            throw new \Exception('Conversation non trouvée ou accès non autorisé.');
        }

        // Déterminer le destinataire (l'autre participant)
        $receiverId = $conversation->traveler_id === $command->travelerId
            ? $conversation->provider_id
            : $conversation->traveler_id;

        // Créer le message
        $this->messageRepository->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $command->travelerId,
            'receiver_id' => $receiverId,
            'content' => $command->content,
            'type' => $command->attachments ? 'image' : 'text',
            'attachments' => $command->attachments,
        ]);

        // Mettre à jour la conversation
        $updateData = ['last_message_at' => now()];
        if ($receiverId === $conversation->provider_id) {
            $updateData['unread_count_provider'] = $conversation->unread_count_provider + 1;
        } else {
            $updateData['unread_count_traveler'] = $conversation->unread_count_traveler + 1;
        }

        $this->conversationRepository->update($conversation, $updateData);

        return $conversation->fresh(['traveler', 'provider', 'experience', 'booking', 'messages']);
    }
}


