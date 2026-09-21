<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class UniversityController extends Controller
{
    /**
     * "Universitet haqida" > "Umumiy ma'lumot" — /universitet/haqida/umumiy
     */
    public function about(): Response
    {
        return Inertia::render('Web/University/About', [
            'settings' => [
                'phone'       => Setting::get('phone'),
                'email'       => Setting::get('email'),
                'address'     => Setting::get('address'),
                'description' => Setting::get('description'),
            ],
        ]);
    }
}
