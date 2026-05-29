<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Finance/Categories', [
            'categories' => auth()->user()->categories()->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        auth()->user()->categories()->create($request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'in:income,expense'],
            'color' => ['required', 'string', 'max:20'],
            'icon' => ['required', 'string', 'max:60'],
        ]));

        return back();
    }
}
