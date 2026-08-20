<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::query()
            ->with('user')
            ->latest();

        // Filter: action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter: user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter: model
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        // Filter: sana
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs'    => $query->paginate(30)->withQueryString(),
            'filters' => $request->only(['action', 'user_id', 'model_type', 'date']),
        ]);
    }
}
