<?php

namespace App\Http\Controllers\Admin;

use App\Admin\AdminTables;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Server-side pagination, filtering and search for the admin tables.
 *
 * GET /admin/api/tables/{table}?page=2&column=subscription&value=pro&search=ana
 * GET /admin/api/tables/{table}/values?column=subscription&q=pro
 */
class AdminTableController extends Controller
{
    public function page(Request $request, string $table, AdminTables $tables): JsonResponse
    {
        abort_unless(AdminTables::exists($table), 404, 'Unknown table.');

        $params = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'column' => ['nullable', 'string', 'max:50'],
            'value' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($tables->page($table, $params));
    }

    public function values(Request $request, string $table, AdminTables $tables): JsonResponse
    {
        abort_unless(AdminTables::exists($table), 404, 'Unknown table.');

        $data = $request->validate([
            'column' => ['required', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($tables->values($table, $data['column'], (string) ($data['q'] ?? '')));
    }
}
