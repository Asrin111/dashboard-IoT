<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function detail($id)
    {
        return view('admin.pages.project_detail', ['id' => $id]);
    }

    public function detailCity($id)
{
    return view('admin.pages.project_detail1', ['id' => $id]);
}

}