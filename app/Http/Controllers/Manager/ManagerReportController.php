<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerReportController extends Controller
{
    private function getAllowedCinemas()
    {
        $user = Auth::user();
        if ($user->roles()->where('name', 'admin')->exists()) {
            return Cinema::withCount('rooms')->get();
        }
        return $user->cinemas()->withCount('rooms')->get();
    }

    public function index(Request $request)
    {
        $cinemas = $this->getAllowedCinemas();

        return view('manager.reports.index', compact('cinemas'));
    }
}
