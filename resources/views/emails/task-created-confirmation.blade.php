<!DOCTYPE html>
<html>
<head>
    <title>Task Created Confirmation</title>
</head>
<body>
    <h1>Task Created: {{ $task->title }}</h1>

    <p>Your task has been successfully created with the following details:</p>

    <ul>
        <li><strong>ID:</strong> TAREFA-{{ $task->id }}</li>
        <li><strong>Title:</strong> {{ $task->title }}</li>
        <li><strong>Description:</strong> {{ $task->description ?? 'N/A' }}</li>
        <li><strong>Priority:</strong> {{ $task->priority }}</li>
        <li><strong>Status:</strong> {{ $task->status }}</li>
        @if($task->due_date)
            <li><strong>Due Date:</strong> {{ $task->due_date }}</li>
        @endif
    </ul>

    <p>To update this task, reply to this email with one of the following commands in the body of your email:</p>
    <ul>
        <li><code>#priority &lt;high|medium|low&gt;</code> - Change the task priority.</li>
        <li><code>#complete</code> - Mark the task as completed.</li>
        <li><code>#comment &lt;your comment text&gt;</code> - Add a comment to the task.</li>
        <li><code>#due &lt;YYYY-MM-DD&gt;</code> - Set or update the due date.</li>
    </ul>

    <p>Thank you!</p>
</body>
</html>