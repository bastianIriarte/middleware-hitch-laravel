<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Users;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.users')->except('logout');
    }

    public function index()
    {
        $users_data = Users::where('deleted', false)->where('id', '!=', 1)->count();

        // $resources = Resource::where('status', 1)
        //     ->where('show_user', 1)
        //     ->get();

        // $resources = $resources->map(function ($resource) {
        //     return (object)[
        //         'id' => $resource->id,
        //         'name' => $resource->name,
        //         'slug' => $resource->slug,
        //         'counts' => $resource->integrationCounts(),
        //     ];
        // });

        // pre_die($resources);

        $title = 'Dashboard';
        $sidenav = 'dashboard';
        $title_table = 'Resumen de Integraciones';
        return view('admin.dashboard', compact(
            'title',
            'sidenav',
            'users_data',
            // 'resources',
            'title_table'
        ));
    }
}
