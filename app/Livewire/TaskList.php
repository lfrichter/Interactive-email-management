<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;

class TaskList extends Component
{
    public function render()
    {
        $tasks = Task::with('comments')->latest()->get();
        return view('livewire.task-list', ['tasks' => $tasks]);
    }
}