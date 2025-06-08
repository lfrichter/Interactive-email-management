<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Task;
use App\Mail\TaskCreatedConfirmation;
use App\Services\CommandParser;

class WebhookController extends Controller
{
    public function emailInbound(Request $request)
    {
        try {
            $subject = $request->input('Subject');
            $fromEmail = $request->input('From');
            $textBody = $request->input('TextBody') ?? '';

            // Check if this is a reply to an existing task
            if (preg_match('/Re: \[TASK-(\d+)\]/', $subject, $matches)) {
                $taskId = $matches[1];
                $task = Task::find($taskId);

                if (!$task) {
                    Log::warning("Task update attempt for non-existent task ID: {$taskId}");
                    return response()->json(['message' => 'Task not found.'], 200);
                }

                // Security: Validate sender
                if ($task->from_email !== $fromEmail) {
                    Log::warning("Unauthorized update attempt for task ID: {$taskId} from {$fromEmail}");
                    return response()->json(['message' => 'Unauthorized.'], 200);
                }

                // Delegate to CommandParser service
                $commandParser = new CommandParser();
                $commandParser->parse($textBody, $task);
                $task->save();

                Log::info("Task ID {$taskId} updated after parsing commands.");

            } else {
                // This is a new task creation
                $task = Task::create([
                    'title' => $subject ?? 'No Subject',
                    'description' => $textBody,
                    'priority' => 'medium', // Default value
                    'status' => 'open', // Default value
                    'due_date' => null,
                    'from_email' => $fromEmail,
                ]);

                // Send confirmation email
                Mail::to($task->from_email)->send(new TaskCreatedConfirmation($task));

                // TODO: Capture and save Postmark Message-ID.
                // Laravel's default mailer might not directly return the Message-ID in a simple way.
                // This might require listening to MessageSent event or using Postmark SDK directly for sending
                // if precise Message-ID capture is critical and not available through standard Mail facade.
                // For now, we'll log that it needs to be implemented.
                Log::info("Task ID {$task->id} created. Confirmation email sent. Message-ID capture pending.");
                // Example if Message-ID were available (hypothetical):
                // if (isset($sentMessage) && method_exists($sentMessage, 'getMessageId')) {
                //     $task->postmark_message_id = $sentMessage->getMessageId();
                //     $task->save();
                // }
            }

            return response()->json(['message' => 'Email processed.'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

}