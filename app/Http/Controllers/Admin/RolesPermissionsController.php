<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class RolesPermissionsController extends Controller
{
    public function index()
    {
        // session()->flash('info', 'Para mudar esses valores, edite <code>config/rolesPermissions.php</code>');
        return view('admin.roles-permissions.show');
        // ->with('info', 'Para mudar esses valores, edite config/rolesPermissions.php');
    }
}
