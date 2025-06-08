<div class="p-6">
    <h1 class="text-xl font-bold mb-4">Received Emails</h1>
    <ul class="space-y-4">
        @foreach ($tasks as $task)
            <li class="border p-4 rounded shadow">
                <p><strong>From:</strong> {{ $task->from_email }}</p>
                <p><strong>Subject:</strong> {{ $task->title }}</p>
                <p><strong>Body:</strong> {{ $task->description }}</p>
                <p><strong>Priority:</strong> {{ $task->priority }}</p>
                <p><strong>Due Date:</strong> {{ $task->due_date ?? 'N/A' }}</p>
            </li>
        @endforeach
    </ul>
</div>
