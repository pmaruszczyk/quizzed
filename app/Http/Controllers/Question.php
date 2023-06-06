<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Question extends Controller
{
    const TIME_PER_QUESTION=10;
    const POINTS_CORRECT=100;

    public function list(?int $index = null) : array
    {
        $questions = [
            [
                'question' => 'Which TV series this picture is referring to?',
                'image' => 'fun.png',
                'answers' => [
                    'A' => 'Bang Bang Theory',
                    'B' => 'Big TechSoup Theory',
                    'C' => 'Fun With Flags',
                    'D' => 'Big Bang Theory',
                ],
                'correct' => 'D',
            ],
            [
                'question' => 'What color is missing here?', //olympic flag
                'image' => 'oly.png',
                'answers' => [
                    'A' => 'orange',
                    'B' => 'purple',
                    'C' => 'red',
                    'D' => 'brown',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'It is the flag of Japan. What the circle represents?',
                'image' => 'jap.png',
                'answers' => [
                    'A' => 'sun',
                    'B' => 'cherry',
                    'C' => 'apple',
                    'D' => 'moon',
                ],
                'correct' => 'A',
            ],
            [
                'question' => 'Captain America seems to be "wearing" the flag from the picture. To which country it belongs?',
                'image' => 'cap.png',
                'answers' => [
                    'A' => 'China',
                    'B' => 'Myanmar',
                    'C' => 'Puerto Rico',
                    'D' => 'Spain',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'What Philippines flag means when it\'s upside-down',
                'image' => 'phi.png',
                'answers' => [
                    'A' => 'national holiday',
                    'B' => 'state of war',
                    'C' => 'nothing',
                    'D' => 'pizza day',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'Ooops! Missing flag here... Choose A',
                'image' => 'mis.png',
                'answers' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                ],
                'correct' => 'A',
            ],
            [
                'question' => 'Which color of Dominica\'s flag is used very rarely on other country flags?',
                'image' => 'dom.png',
                'answers' => [
                    'A' => 'green',
                    'B' => 'yellow',
                    'C' => 'white',
                    'D' => 'purple',
                ],
                'correct' => 'D',
            ],
            [
                'question' => 'Wanna machine gun? Sure, it\s in flag of ...',
                'image' => 'moz.png',
                'answers' => [
                    'A' => 'Vietnam',
                    'B' => 'Mozambique',
                    'C' => 'Armenia',
                    'D' => 'Poland',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'What is the pirates flag name?',
                'image' => 'pir.png',
                'answers' => [
                    'A' => 'Holy Molly',
                    'B' => 'Bald Man',
                    'C' => 'Jolly Roger',
                    'D' => 'Pretty Face',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'What star constellation is presented on the left?',
                'image' => 'aus.png',
                'answers' => [
                    'A' => 'Southern Cross',
                    'B' => 'Aquarius',
                    'C' => 'Phoenix',
                    'D' => 'Canis Major',
                ],
                'correct' => 'A',
            ],
            [
                'question' => 'To what country that flag belongs to?',
                'image' => 'aus.png',
                'answers' => [
                    'A' => 'South Africa',
                    'B' => 'Peru',
                    'C' => 'New Zealand',
                    'D' => 'Australia',
                ],
                'correct' => 'D',
            ],
            [
                'question' => 'Country Swaziland changed its name to ....',
                'image' => 'sw.png',
                'answers' => [
                    'A' => 'Eswatini',
                    'B' => 'Wichooti',
                    'C' => 'Rwanda',
                    'D' => 'John',
                ],
                'correct' => 'A',
            ],
            [
                'question' => 'Three legs to represents three corners of island called...',
                'image' => 'is.png',
                'answers' => [
                    'A' => 'Greenland',
                    'B' => 'Australia',
                    'C' => 'Sicily',
                    'D' => 'Malta',
                ],
                'correct' => 'C',
            ],
        ];

        $questions = array_merge([[]], $questions);

        if ($index!== null) {
            if (isset($questions[$index])) {
                return $questions[$index];
            }
            else {
                return [];
            }
        }

        return $questions;

    }

    private function getCurrentFull() : array
    {
        $index = $this->getCurrentQuestionIndex();
        $question = $this->list($index);
        if ($index>0 && empty($question)) {
            $question['id'] = '-1';
        } else {
            $question['id'] = $index;
        }
        $question['time_per_question'] = self::TIME_PER_QUESTION;

        return $question;
    }

    public function getCurrent() : array
    {
        $question = $this->getCurrentFull();

        if (!$this->isAnswerRevealed()) {
            unset($question['correct']);
        }

        return $question;
    }

    private function isAnswerRevealed() {
        $result = DB::select("select value FROM state WHERE id='SHOWANSWER'", []);
        return (int) $result[0]->value;
    }

    private function getCurrentQuestionIndex() {
        $result = DB::select("select value FROM state WHERE id='STEP'", []);
        return (int) $result[0]->value;
    }

    private function getCurrentStartTime() {
        $result = DB::select("select value FROM state WHERE id='STEPSTARTTIME'", []);
        return (int) $result[0]->value;
    }

    public function saveAnswer(Request $request) {
        $answer = $request->input('answer');
        $index = $this->getCurrentQuestionIndex();
        $startTime = $this->getCurrentStartTime();
        $now = time();

        $question = $this->getCurrentFull($index);

        $points = 0;

        if ($question['correct'] === $answer && ($startTime+self::TIME_PER_QUESTION > $now)) {
            $points += self::POINTS_CORRECT;

//            if (true) {
                $points += 2 * ($startTime+self::TIME_PER_QUESTION-$now);
//            }
//            return var_export([$startTime+self::TIME_PER_QUESTION, $now], true);
        }

        DB::insert('insert into answers (nick, question, answer, points, time) values (?,?, ?, ?, ?)',
            [
                $request->session()->get('nick'),
                $index,
                $answer,
                $points,
                $now
            ]);

        return $points;
    }

}
