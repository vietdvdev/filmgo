<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\HomeService;

class HomeController extends Controller
{
    public function __construct(
        protected HomeService $homeService
    ) {}

    public function index()
    {
        $homeData = $this->homeService->getHomePageData();

        return view('home', $homeData);
    }
}
