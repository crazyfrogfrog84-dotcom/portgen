<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class HealthController extends Controller
{
    public function __invoke(): Response
    {
        return response('OK', 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
