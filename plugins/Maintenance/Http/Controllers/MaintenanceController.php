<?php

namespace Plugins\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        return view('maintenance::index');
    }
}
