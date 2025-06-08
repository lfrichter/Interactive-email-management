<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Log;

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
        // Usamos um array associativo de [padrão_regex => função_handler]
        // O modificador 'i' torna a regex case-insensitive.
        $commands = [
            '/#(priority|prioridade)\s+(high|medium|low|alta|media|baixa)/i' => function ($matches) use ($task) {
                // Normaliza os valores para o padrão do banco de dados
                $priorityMap = [
                    'alta' => 'high',
                    'media' => 'medium',
                    'baixa' => 'low',
                ];
                $normalizedPriority = strtolower($matches[2]);
                $task->priority = $priorityMap[$normalizedPriority] ?? $normalizedPriority;
            },
            '/#concluir/i' => function () use ($task) {
                $task->status = 'completed';
            },
            '/#due\s+(\d{4}-\d{2}-\d{2})/i' => function ($matches) use ($task) {
                $task->due_date = $matches[1];
            },
        ];

        foreach ($commands as $pattern => $handler) {
            // Usamos preg_match para encontrar a primeira ocorrência de cada comando
            if (preg_match($pattern, $body, $matches)) {
                $handler($matches);
            }
        }
    }
}
