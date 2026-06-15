<?php

namespace App\Http\Controllers\Admin;

// Kế thừa trực tiếp từ Class Routing gốc của Laravel để tránh lỗi thiếu file Controller.php
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function index()
    {
        // Trả về giao diện trang chủ Admin của nhóm
        return view('admin.dashboard');
    }
}
