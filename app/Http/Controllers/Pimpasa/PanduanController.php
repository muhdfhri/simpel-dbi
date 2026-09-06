<?php

namespace App\Http\Controllers\Pimpasa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PanduanController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Pimpasa/Panduan/Index');
    }
}
