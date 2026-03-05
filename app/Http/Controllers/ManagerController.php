<?php

namespace App\Http\Controllers;

use App\Models\RaceResult;
use App\Models\User;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function dashboard(): View
    {
        $driverCount = User::where('role', 'driver')->count();

        return view('manager.dashboard', [
            'driverCount' => $driverCount,
        ]);
    }

    public function leaderboard(): View
    {
        $drivers = User::query()
            ->where('role', 'driver')
            ->with('driverStats')
            ->get()
            ->sortByDesc(fn (User $driver) => $driver->driverStats?->irating ?? 0)
            ->values();

        return view('manager.leaderboard', [
            'drivers' => $drivers,
        ]);
    }

    public function calendar(): View
    {
        $upcoming = RaceResult::query()
            ->whereNotNull('race_date')
            ->where('race_date', '>=', now())
            ->orderBy('race_date')
            ->limit(20)
            ->get();

        return view('manager.calendar', [
            'upcoming' => $upcoming,
        ]);
    }
}
