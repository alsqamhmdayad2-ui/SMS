<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $parent = ParentModel::where('user_id', $user->id)->first();

        return view('panels.parent.profile', compact('parent'));
    }
}
