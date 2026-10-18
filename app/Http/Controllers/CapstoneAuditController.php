<?php

namespace App\Http\Controllers;

use App\Models\CapstoneAudit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CapstoneAuditController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Only administrators may view audit history.
        if (! $user || $user->role !== 'admin') {
            abort(403, 'Only administrators may view audit history.');
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'max:100'],
        ]);

        $query = CapstoneAudit::with([
            'user',
            'capstoneProject',
        ])->latest();

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('capstone_project_id', $search)
                    ->orWhere('user_id', $search);
            });
        }

        if (! empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        $audits = $query->paginate(15)->withQueryString();

        $actions = CapstoneAudit::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.capstone-audits', compact(
            'audits',
            'actions'
        ));
    }
}
