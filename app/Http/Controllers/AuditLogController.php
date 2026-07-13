<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        // Must be authorized in routes, but can add check here or policy
        if (auth()->user()->role?->slug !== 'super-admin') {
            abort(403, 'Unauthorized access.');
        }

        $auditLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(50);
        return view('audit-logs.index', [
            'title' => 'Audit Logs',
            'auditLogs' => $auditLogs,
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        if (auth()->user()->role?->slug !== 'super-admin') {
            abort(403, 'Unauthorized access.');
        }
        
        $auditLog->load('user');
        return view('audit-logs.show', [
            'title' => 'Audit Log Detail',
            'auditLog' => $auditLog,
        ]);
    }
}
