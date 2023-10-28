<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetNick extends Controller
{
    public function index(Request $request)
    {
        if ($request->session()->get('nick')) {
            return redirect('/user');
        }

        return view('welcome');
    }

    public function set(Request $request)
    {
        if (empty($request->input('nick'))) {
            throw new \Exception();
        }

        $x = $request->input('nick') . '_' . random_int(90000,99999);
        $request->session()->put('nick', $x);

        DB::insert('INSERT INTO nick (nick) VALUES (?)', [$x]);

        return ['id' => true];
    }
}
