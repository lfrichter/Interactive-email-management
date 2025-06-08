<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Task;

class WebhookController extends Controller
{
    public function emailInbound(Request $request)
    {
        try {
            Task::create([
                'title' => $request->input('Subject') ?? 'No Subject',
                'description' => $request->input('TextBody') ?? '',
                'priority' => 'medium',
                'due_date' => null,
                'from_email' => $request->input('From'),
            ]);
            return response()->json(['message' => 'Email processed.'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

}