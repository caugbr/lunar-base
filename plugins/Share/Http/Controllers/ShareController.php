<?php

namespace Plugins\Share\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function index()
    {
        return view('share::index');
    }
}
