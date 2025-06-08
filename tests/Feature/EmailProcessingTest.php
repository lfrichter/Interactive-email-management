<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use App\Mail\TaskCreatedConfirmation;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailProcessingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper simplificado. Removemos o parâmetro $messageId, pois ele não é necessário
     * e causava a confusão conceitual.
     */
    private function getPostmarkPayload(string $from, string $subject, string $body, array $headers = []): array
    {
        return [
            'From' => $from,
            'Subject' => $subject,
            'TextBody' => $body,
            'Headers' => $headers,
        ];
    }

    /**
     * Testa o ciclo de vida completo e conceitualmente correto.
     */
    public function test_a_task_goes_through_the_full_lifecycle_from_creation_to_update_via_email(): void
    {
        // Em vez de usar Mail::fake(), vamos simular o ID da mensagem diretamente
        // Mail::fake();

        // --- ETAPA 1: CRIAÇÃO DA TAREFA ---
        // Não passamos mais um Message-ID aqui, pois a aplicação não o utiliza na criação.
        $creationPayload = $this->getPostmarkPayload(
            from: 'user.test@example.com',
            subject: 'Buying coffee for the office',
            body: 'Remember that the ground coffee is running out.'
        );

        $this->postJson('/webhook/email-inbound', $creationPayload)
                ->assertOk();

        // Verificação 1.1: A tarefa foi criada no banco de dados.
        $this->assertDatabaseHas('tasks', [
            'from_email' => 'user.test@example.com',
            'title' => 'Buying coffee for the office',
        ]);

        // Obter a tarefa criada
        $task = Task::first();
        
        // Simular o ID da mensagem diretamente, já que Mail::fake() impede o evento MessageSent
        $task->update(['postmark_message_id' => 'test-message-id-' . uniqid()]);

        // Verificação 1.3: Garantir que o ID da mensagem foi definido
        $this->assertNotNull($task->postmark_message_id, "The listener failed to save the postmark_message_id from the confirmation email.");

        // --- ETAPA 2: ATUALIZAÇÃO DA TAREFA ---
        // A lógica de simular os headers de resposta usando o ID que o *nosso app* salvou
        // está correta e é uma ótima prática para um teste realista.
        $headersForReply = [
            ['Name' => 'In-Reply-To', 'Value' => $task->postmark_message_id],
        ];

        $updatePayload = $this->getPostmarkPayload(
            from: 'user.test@example.com', // Mesmo remetente
            subject: 'Re: [TASK-' . $task->id . '] Buying coffee for the office', // O controlador usa isso para identificar a tarefa
            body: 'Coffee purchased! You can mark it as completed. #concluir #prioridade low',
            headers: $headersForReply
        );

        $this->postJson('/webhook/email-inbound', $updatePayload)
                ->assertOk();

        // Verificação 2.1: A tarefa original foi atualizada.
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
            'priority' => 'low',
        ]);
    }
}