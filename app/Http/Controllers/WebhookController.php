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
            $parser = new CommandParser();

            // Check if this is a reply to an existing task
            if (preg_match('/Re: \[TASK-(\d+)\]/', $subject, $matches)) {
                $taskId = $matches[1];
                $task = Task::find($taskId);

                if (!$task) {
                    Log::warning("Task update attempt for non-existent task ID: {$taskId}");
                    return response()->json(['message' => 'Task not found.'], 200);
                }
                if ($task->from_email !== $fromEmail) {
                    Log::warning("Unauthorized update attempt for task ID: {$taskId} from {$fromEmail}");
                    return response()->json(['message' => 'Unauthorized.'], 200);
                }

                // Para respostas, processamos os comandos mas não precisamos da descrição limpa.
                $parser->parse($textBody, $task);
                $task->save();

                Log::info("Task ID {$taskId} updated after parsing commands.");

            } else {
                // --- LÓGICA DE CRIAÇÃO ATUALIZADA ---
                // 1. Cria uma instância da Tarefa em memória (sem salvar) com os dados básicos.
                $task = new Task([
                    'title' => $subject ?? 'No Subject',
                    'from_email' => $fromEmail,
                    // Valores padrão que podem ser sobrescritos pelo parser
                    'priority' => 'medium',
                    'status' => 'open',
                ]);

                // 2. Chama o parser. Ele vai:
                //    a) Modificar o objeto $task com os comandos encontrados (ex: prioridade, data).
                //    b) Retornar o corpo do texto sem as linhas de comando.
                $cleanedDescription = $parser->parse($textBody, $task);

                // 3. Atribui a descrição limpa à tarefa.
                $task->description = $cleanedDescription;

                // 4. Salva a tarefa totalmente configurada no banco de dados.
                $task->save();

                // 5. Envia o e-mail de confirmação (nenhuma mudança aqui).
                Mail::to($task->from_email)->send(new TaskCreatedConfirmation($task));
                Log::info("Task ID {$task->id} created. Confirmation email sent.");
            }

            return response()->json(['message' => 'Email processed.'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

}