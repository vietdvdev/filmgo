<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }
}
