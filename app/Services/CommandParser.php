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
        // TODO: Implement command parsing logic using regex
        // Example commands to parse:
        // #priority <high|medium|low>
        // #complete
        // #comment <text>
        // #due <YYYY-MM-DD>

        // For now, we'll just log that parsing is pending.
        // \Illuminate\Support\Facades\Log::info("CommandParser: Parsing email body for task ID {$task->id}. Body: {$body}");
    }
}