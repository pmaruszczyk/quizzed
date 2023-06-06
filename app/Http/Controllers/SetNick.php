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

        $x = $request->input('nick') . '_' . mt_rand(90000,99999);
        $request->session()->put('nick', $x);

        DB::insert('insert into nick (nick) values (?)', [$x]);
        $result = DB::select("select id from nick where nick =?", [$x]);

        return [
            'id' => $result[0]->id,
            'nickFromServer' => $x,
        ];
    }
}
