<?php

namespace App\SalesAdmin\Controllers;

use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        // return $content
        //     ->header('Dashboard')
        //     ->description('Description...')
        //     ->body(function (Row $row) {
        //     });
        // dd(Admin::user()->subordinates());
        return $content;
    }
}
