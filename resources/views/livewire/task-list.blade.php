<div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <x-heroicon-o-inbox-stack class="w-8 h-8 mr-3 text-indigo-600"/>
        Received Tasks & Comments
    </h1>
    <ul class="space-y-4">
        @foreach ($tasks as $task)
            <li class="border p-4 rounded-lg shadow-md bg-white">
                {{-- Detalhes da Tarefa --}}
                <div class="grid grid-cols-2 gap-x-4 mb-2">
                    <p class="flex items-center text-sm text-gray-600">
                        <x-heroicon-o-user-circle class="w-5 h-5 mr-2 text-gray-400"/>
                        <strong>From:</strong><span class="ml-1">{{ $task->from_email }}</span>
                    </p>
                    <p class="flex items-center text-sm text-gray-600">
                        <x-heroicon-o-calendar class="w-5 h-5 mr-2 text-gray-400"/>
                        <strong>Due Date:</strong><span class="ml-1">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'N/A' }}</span>
                    </p>
                    <p class="flex items-center text-sm text-gray-600 mt-1">
                        {{-- Ícone e cor da prioridade mudam dinamicamente --}}
                        @if($task->priority == 'high')
                            <x-heroicon-s-flag class="w-5 h-5 mr-2 text-red-500"/>
                            <strong>Priority:</strong><span class="ml-1 font-semibold text-red-600 uppercase">{{ $task->priority }}</span>
                        @elseif($task->priority == 'medium')
                            <x-heroicon-s-flag class="w-5 h-5 mr-2 text-yellow-500"/>
                            <strong>Priority:</strong><span class="ml-1 font-semibold text-yellow-600 uppercase">{{ $task->priority }}</span>
                        @else
                            <x-heroicon-s-flag class="w-5 h-5 mr-2 text-green-500"/>
                            <strong>Priority:</strong><span class="ml-1 font-semibold text-green-600 uppercase">{{ $task->priority }}</span>
                        @endif
                    </p>

                    {{-- [NOVO] Bloco de Status da Tarefa --}}
                    <p class="flex items-center text-sm text-gray-600 mt-1">
                        @if($task->status == 'completed')
                            <x-heroicon-s-check-circle class="w-5 h-5 mr-2 text-green-500"/>
                            <strong>Status:</strong><span class="ml-1 font-semibold text-green-600 uppercase">{{ $task->status }}</span>
                        @else
                            <x-heroicon-s-ellipsis-horizontal-circle class="w-5 h-5 mr-2 text-blue-500"/>
                            <strong>Status:</strong><span class="ml-1 font-semibold text-blue-600 uppercase">{{ $task->status }}</span>
                        @endif
                    </p>
                </div>

                <h2 class="text-lg font-bold text-gray-800 mt-2">{{ $task->title }}</h2>
                <p class="mt-1 text-gray-700">{{ $task->description }}</p>

                {{-- Seção de Comentários --}}
                @if ($task->comments->isNotEmpty())
                    <div class="mt-4 pt-3 border-t border-gray-200">
                        <h3 class="text-md font-semibold mb-2 flex items-center text-gray-700">
                            <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 mr-2"/>
                            Comments
                        </h3>
                        <ul class="space-y-3 pl-4 mt-2">
                            @foreach ($task->comments as $comment)
                                <li class="text-sm border-l-2 border-gray-300 pl-3">
                                    <p class="text-gray-800">{{ $comment->body }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        - by {{ $comment->from_email }} on {{ $comment->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
