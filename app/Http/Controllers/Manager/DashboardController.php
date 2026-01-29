<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\ManagerDashboardData;

class DashboardController extends Controller
{
    public function index()
    {
        $data = app(ManagerDashboardData::class)
            ->build(auth()->user());

        return view('manager.dashboard', $data);
    }
}
