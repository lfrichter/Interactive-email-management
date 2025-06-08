<?php

namespace Tests\Unit;

use App\Services\CommandParser;
use App\Models\Task;

// Teste para o parser de prioridade
test('it correctly parses the priority command', function () {
    $parser = new CommandParser();
    $task = new Task(['priority' => 'medium']);
    $body = "Obrigado pela criação!\n#prioridade alta";

    $parser->parse($body, $task);

    expect($task->priority)->toBe('high');
});

// Teste para o comando de conclusão
test('it correctly parses the complete command', function () {
    $parser = new CommandParser();
    $task = new Task(['status' => 'open']);
    $body = "#concluir"; // O comando no código estava como #complete, mas na view era #concluir. Usando #concluir.

    $parser->parse($body, $task);

    expect($task->status)->toBe('completed');
});

// Teste para o comando de data de entrega (implementado)
test('it correctly parses the due date command', function () {
    $parser = new CommandParser();
    $task = new Task();
    $body = "Essa tarefa precisa ser feita até o dia 15.\n#due 2025-07-15";

    $parser->parse($body, $task);

    expect($task->due_date)->toBe('2025-07-15');
});


// Teste para múltiplos comandos
test('it handles multiple commands in one email', function () {
    // Usar factory() cria um objeto Task sem salvar no banco, ideal para testes unitários.
    $task = Task::factory()->make([
        'priority' => 'high',
        'due_date' => null,
    ]);
    $parser = new CommandParser(); // Added missing instantiation
    $body = "Ok, entendido.\n#prioridade low\n#due 2025-12-31";

    $parser->parse($body, $task);

    expect($task->priority)->toBe('low')
        ->and($task->due_date)->toBe('2025-12-31');
});

// Teste para ignorar texto irrelevante
test('it ignores text that is not a command', function () {
    $task = Task::factory()->make(['priority' => 'medium']);
    $parser = new CommandParser();
    $body = "Acho que a prioridade deveria ser outra, mas tudo bem.";

    $parser->parse($body, $task);

    // A prioridade não deve mudar
    expect($task->priority)->toBe('medium');
});