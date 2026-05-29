<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\FinanceDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(FinanceDashboardService $dashboard): Response
    {
        return Inertia::render('Finance/Dashboard', [
            'dashboard' => $dashboard->forUser(auth()->user()),
        ]);
    }
}
