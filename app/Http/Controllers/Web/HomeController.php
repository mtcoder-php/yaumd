<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Faculty;
use App\Models\NewsArticle;
use App\Models\Partner;
use App\Models\QuickLink;
use App\Models\Setting;
use App\Models\Slider;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Web/Home', [
            'sliders'    => Slider::where('is_active', true)
                ->orderBy('order')
                ->get(),
            'news'       => NewsArticle::with('category')
                ->where('is_published', true)
                ->latest('published_at')
                ->take(6)
                ->get(),
            'directions' => Direction::with('faculty')
                ->where('is_active', true)
                ->take(6)
                ->get(),
            'partners'   => Partner::where('is_active', true)
                ->orderBy('order')
                ->get(),
            'quickLinks' => QuickLink::where('is_active', true)
                ->orderBy('order')
                ->get(),
            'faculties'  => Faculty::where('is_active', true)
                ->get(['id', 'name_uz', 'name_ru', 'name_en']),
            'settings'   => [
                'phone'       => Setting::get('phone'),
                'email'       => Setting::get('email'),
                'address'     => Setting::get('address'),
                'description' => Setting::get('description'),
            ],
        ]);
    }
}
