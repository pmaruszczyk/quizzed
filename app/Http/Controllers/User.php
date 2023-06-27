<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class User extends Controller
{
    public function index(string $nick, Request $request)
    {
        $nickFromSession = $request->session()->get('nick');

        if ($nick !== $nickFromSession) {
            return redirect('/');
        }

        $result = DB::select("SELECT id FROM nick WHERE nick = ?", [$nick]);

        if (empty($result)) {
            $request->session()->put('nick',false);
            return redirect('/');
        }

        return view('question',[
            'id' => $result[0]->id,
            'nickFromServer' => $nick,
        ]);
    }
}
