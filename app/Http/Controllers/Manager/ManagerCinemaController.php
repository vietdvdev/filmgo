<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagerCinemaController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->cinemas()->withCount('rooms');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cinemas.name', 'like', '%' . $request->search . '%')
                  ->orWhere('cinemas.city', 'like', '%' . $request->search . '%');
            });
        }

        $cinemas = $query->latest('cinemas.id')->get();

        return view('manager.cinemas.index', compact('cinemas'));
    }
}
