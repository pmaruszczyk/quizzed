<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Admin extends Controller
{
    public function adminIndex(Request $request)
    {
        if (!$request->session()->get('admin', false)) {
            return redirect('/');
        }

        return view('admin');
    }

    public function makeMeAdmin(Request $request)
    {
        $request->session()->put('admin', TRUE);
        return redirect('/admin');
    }

    public function users(Request $request)
    {
        return DB::select("
            SELECT n.nick, IF(b.nick IS NULL, 0, 1) AS answered , IFNULL(SUM(a.points), 0) AS points FROM nick n
            LEFT JOIN answers a
            ON n.nick=a.nick
            LEFT JOIN answers b
            ON n.nick=b.nick AND (SELECT value FROM state WHERE id='STEP') = b.question

            WHERE n.nick IS NOT NULL
            GROUP BY n.nick, a.nick, b.nick
            ORDER BY points DESC
        ");
    }

    public function goToNextStep(Request $request)
    {
        if (!$request->session()->get('admin', false)) {
            return response('Sorry', 400);
        }

        DB::update("UPDATE state SET value=value+1 WHERE id='STEP'");
        DB::update("UPDATE state SET value=? WHERE id='STEPSTARTTIME'", [time()]);
        DB::update("UPDATE state SET value=? WHERE id='SHOWANSWER'", [0]);

        return $this->getCurrentQuestionIndex();
    }

    private function getCurrentQuestionIndex()
    {
        $result = DB::select("SELECT value FROM state WHERE id='STEP'", []);
        return (int) $result[0]->value;
    }

    public function showAnswer(Request $request)
    {
        if (!$request->session()->get('admin', false)) {
            return response('Sorry', 400);
        }

        DB::update("UPDATE state SET value=? WHERE id='SHOWANSWER'", [1]);

        return true;
    }

}
