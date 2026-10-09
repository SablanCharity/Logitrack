<?php

namespace App\Http\Controllers;

use App\Models\CapstoneProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepositoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $capstones = CapstoneProject::with([
            'groupRoom.classroom.instructor',
            'groupRoom.members.user',
        ])
            ->where('status', 'admin_approved')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('abstract', 'like', '%' . $search . '%')
                        ->orWhere('program', 'like', '%' . $search . '%')
                        ->orWhere('year_section', 'like', '%' . $search . '%');
                });
            })
            ->latest('approved_at')
            ->paginate(10);

        return view('repository.index', compact(
            'capstones',
            'search'
        ));
    }

    public function show(int $capstoneId): View
        {
            $capstone = CapstoneProject::with([
                'groupRoom.classroom.instructor',
                'groupRoom.members.user',
            ])
                ->where('status', 'admin_approved')
                ->findOrFail($capstoneId);

            return view('repository.show', compact('capstone'));
        }

}