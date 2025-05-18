<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user();
        $user->load('dashboardPreference');

        return response()->json($user);
    }
}
