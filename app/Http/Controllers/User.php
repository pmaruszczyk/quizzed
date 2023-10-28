<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class User extends Controller
{
    public function index(Request $request)
    {
        $nick = $request->session()->get('nick');

        if (empty($nick)) {
            return redirect('/');
        }

        $result = DB::select("SELECT id FROM nick WHERE nick = ?", [$nick]);

        if (empty($result)) {
            $request->session()->put('nick', false);
            return redirect('/');
        }

        return view('player-screen');
    }
}
