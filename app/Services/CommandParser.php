<?php

namespace App\Services;

use App\Models\Task;

class CommandParser
{
    /**
     * Parses the email body for commands and updates the task.
     *
     * @param string $body The email body text.
     * @param Task $task The task to update.
     * @return void
     */
    public function parse(string $body, Task $task): void
    {
        $commands = [
            '/#priority\s+(high|medium|low)/i' => fn($matches) => $task->priority = strtolower($matches[1]),
            '/#complete/i' => fn() => $task->status = 'completed',
            '/#due\s+(\d{4}-\d{2}-\d{2})/i' => fn($matches) => $task->due_date = $matches[1],
            // Regex for #comment, captures everything after #comment until the end of the line or email
            '/#comment\s+(.+)/i' => function($matches) use ($task) {
                // Append new comment to existing description, or set if description is empty
                $newComment = trim($matches[1]);
                if (!empty($task->description)) {
                    $task->description .= "\n---\nComment: " . $newComment;
                } else {
                    $task->description = "Comment: " . $newComment;
                }
            },
        ];

        // Normalize line endings to ensure regex works consistently
        $normalizedBody = str_replace("\r\n", "\n", $body);
        $lines = explode("\n", $normalizedBody);

        foreach ($lines as $line) {
            foreach ($commands as $pattern => $handler) {
                if (preg_match($pattern, $line, $matches)) {
                    $handler($matches);
                    // Log which command was processed for debugging
                    // \Illuminate\Support\Facades\Log::info("CommandParser: Processed command matching {$pattern} for task ID {$task->id}");
                }
            }
        }
    }
}