<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Comment;

class CommandParser
{
    /**
     * Parses the email body for commands, updates the task,
     * and returns the body text with the commands removed.
     *
     * @param string $body The email body text.
     * @param Task $task The task to update.
     * @return string The cleaned body text.
     */
    public function parse(string $body, Task $task): string
    {
        $cleanedBody = $body;

        $commands = [
            '/#priority\s+(high|medium|low)/i' => function ($matches) use ($task, &$cleanedBody) {
                $task->priority = strtolower($matches[1]);
                // Remove a linha inteira que contém o comando
                $cleanedBody = preg_replace('/^.*' . preg_quote($matches[0], '/') . '.*\R?/m', '', $cleanedBody);
            },
            '/#complete/i' => function ($matches) use ($task, &$cleanedBody) {
                $task->status = 'completed';
                $cleanedBody = preg_replace('/^.*' . preg_quote($matches[0], '/') . '.*\R?/m', '', $cleanedBody);
            },
            '/#due\s+(\d{4}-\d{2}-\d{2})/i' => function ($matches) use ($task, &$cleanedBody) {
                $task->due_date = $matches[1];
                $cleanedBody = preg_replace('/^.*' . preg_quote($matches[0], '/') . '.*\R?/m', '', $cleanedBody);
            },
            '/#comment\s+(.*)/is' => function ($matches) use ($task, &$cleanedBody) {
                $commentText = trim($matches[1]);
                if (!empty($commentText)) {
                    Comment::create([
                        'task_id' => $task->id,
                        'body' => $commentText,
                        'from_email' => $task->from_email,
                    ]);
                }
                $cleanedBody = preg_replace('/^.*' . preg_quote($matches[0], '/') . '.*\R?/m', '', $cleanedBody);
            },
        ];

        foreach ($commands as $pattern => $handler) {
            if (preg_match($pattern, $body, $matches)) {
                $handler($matches);
            }
        }

        // Retorna o corpo do texto limpo e sem espaços extras
        return trim($cleanedBody);
    }
}