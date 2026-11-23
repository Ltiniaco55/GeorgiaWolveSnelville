<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Home page. In the local environment, ?intro=1 plays the brand intro even
     * when it was already seen in this session, to review it.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Home', [
            'forceIntro' => app()->isLocal() && $request->boolean('intro'),
        ]);
    }
}
