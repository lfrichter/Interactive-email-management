<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\Task;
use Illuminate\Support\Facades\Log; // Added for logging

class StorePostmarkMessageId
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Mail\Events\MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event): void
    {
        // Check if the email being sent has our Task object in its data array
        // This data is passed via the `with` method in the Mailable's content definition
        if (isset($event->data['task']) && $event->data['task'] instanceof Task) {
            $task = $event->data['task'];

            // Get the Message-ID directly from the message
            $message = $event->message;
            $headers = $message->getHeaders();
            
            if ($headers->has('Message-ID')) {
                $messageId = $headers->get('Message-ID')->getBodyAsString();
                // The Message-ID from headers often includes angle brackets, remove them.
                $cleanedMessageId = trim($messageId, '<>');
                $task->update(['postmark_message_id' => $cleanedMessageId]);
                Log::info("Stored Postmark Message-ID: {$cleanedMessageId} for Task ID: {$task->id}");
            } else {
                // If no Message-ID is found, generate a unique one
                $generatedId = 'task-' . $task->id . '-' . uniqid();
                $task->update(['postmark_message_id' => $generatedId]);
                Log::info("Generated Message-ID: {$generatedId} for Task ID: {$task->id}");
            }
        } else {
            // Log if the task data isn't found, helps in debugging if Message-IDs aren't saving
            Log::debug('MessageSent event handled, but no Task data found in event data.', ['event_data_keys' => array_keys($event->data)]);
        }
    }
}