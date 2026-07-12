<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class RolesPermissionsController extends Controller
{
    public function index()
    {
        return view('admin.roles-permissions.show');
    }
}
