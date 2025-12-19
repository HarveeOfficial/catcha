<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $admins = User::where('role', 'admin')->count();
        $experts = User::where('role', 'expert')->count();
        $maos = User::where('role', 'mao')->count();
        $regularUsers = User::where('role', 'user')->orWhereNull('role')->count();
        $recentUsers = User::orderByDesc('created_at')->limit(5)->get();

        return view('superadmin.dashboard', compact(
            'totalUsers',
            'admins',
            'experts',
            'maos',
            'regularUsers',
            'recentUsers'
        ));
    }
}
