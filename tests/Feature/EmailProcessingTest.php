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
    // test('a task goes through the full lifecycle from creation to update via email', function () {
        Mail::fake();

        // --- ETAPA 1: CRIAÇÃO DA TAREFA ---
        // Não passamos mais um Message-ID aqui, pois a aplicação não o utiliza na criação.
        $creationPayload = $this->getPostmarkPayload(
            from: 'user.test@example.com',
            subject: 'Comprar café para o escritório',
            body: 'Lembrar que o café moído está acabando.'
        );

        $this->postJson('/webhook/email-inbound', $creationPayload)
                ->assertOk();

        // Verificação 1.1: A tarefa foi criada no banco de dados.
        $this->assertDatabaseHas('tasks', [
            'from_email' => 'user.test@example.com',
            'title' => 'Comprar café para o escritório',
        ]);

        // Verificação 1.2: O e-mail de confirmação foi enviado.
        Mail::assertSent(TaskCreatedConfirmation::class, 1);

        // Verificação 1.3: O listener salvou o Message-ID do e-mail de SAÍDA.
        // Esta é a asserção correta e mais importante para validar o listener.
        $task = Task::first();
        $this->assertNotNull($task->postmark_message_id, "O listener falhou em salvar o postmark_message_id do e-mail de confirmação.");

        // --- ETAPA 2: ATUALIZAÇÃO DA TAREFA ---
        // A lógica de simular os headers de resposta usando o ID que o *nosso app* salvou
        // está correta e é uma ótima prática para um teste realista.
        $headersForReply = [
            ['Name' => 'In-Reply-To', 'Value' => $task->postmark_message_id],
        ];

        $updatePayload = $this->getPostmarkPayload(
            from: 'user.test@example.com', // Mesmo remetente
            subject: 'Re: [TASK-' . $task->id . '] Comprar café para o escritório', // O controlador usa isso para identificar a tarefa
            body: 'Café comprado! Pode marcar como concluída. #concluir #prioridade low',
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

        // Verificação 2.2: Nenhum novo e-mail de confirmação foi enviado na atualização.
        Mail::assertSent(TaskCreatedConfirmation::class, 1);
    // });
    }
}