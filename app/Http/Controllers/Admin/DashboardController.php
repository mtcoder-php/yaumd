<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'students'    => 0,
                'applicants'  => 0,
                'courses'     => 0,
                'books'       => 0,
            ],
        ]);
    }
}
