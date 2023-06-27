<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetNick extends Controller
{
    public function index(Request $request)
    {
        if ($request->session()->get('nick')) {
            return redirect('/user/' . $request->session()->get('nick'));
        }

        return view('welcome');
    }

    public function set(Request $request)
    {
        $x = $request->input('nick') . '_' . random_int(90000,99999);
        $request->session()->put('nick', $x);

        DB::insert('INSERT INTO nick (nick) VALUES (?)', [$x]);
        $result = DB::select("SELECT id FROM nick WHERE nick =?", [$x]);

        return [
            'id' => $result[0]->id,
            'nickFromServer' => $x,
        ];
    }
}
