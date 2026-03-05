<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DriverController extends Controller
{
    public function profile(): View
    {
        $user = auth()->user();

        return view('driver.profile', [
            'user' => $user,
            'stats' => $user?->driverStats,
            'results' => $user?->raceResults()->latest('race_date')->limit(10)->get() ?? collect(),
        ]);
    }
}
