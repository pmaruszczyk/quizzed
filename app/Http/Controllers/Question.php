<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Question extends Controller
{
    private const TIME_PER_QUESTION = 30;
    private const POINTS_CORRECT = 100;

    public function list(?int $index = null) : array
    {
        //TODO Move to config file
        $questions = [
            [
                'question' => 'I was going to make myself a belt made out of watches, but then I realized it would be ....',
                'image' => 'belt.jpg',
                'answers' => [
                    'A' => 'strange',
                    'B' => 'too expensive',
                    'C' => 'a waist of time',
                    'D' => 'extravagant',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'One windmill asks the other: What\'s your favorite kind of music?" The other says:',
                'image' => 'wind.png',
                'answers' => [
                    'A' => 'Huh?',
                    'B' => 'I have none:(',
                    'C' => 'Disco Polo',
                    'D' => 'I\'m a big metal fan',
                ],
                'correct' => 'D',
            ],
            [
                'question' => 'I wasn\'t originally going to get a brain transplant, but then...',
                'image' => 'brain.jpg',
                'answers' => [
                    'A' => 'I faced it',
                    'B' => 'I changed my mind',
                    'C' => 'I loved it',
                    'D' => 'I will not answer this question',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'I suffer from kleptomania, but when it gets really bad...',
                'image' => 'klep.png',
                'answers' => [
                    'A' => 'I phone my friend',
                    'B' => 'I drink hot chocolate',
                    'C' => 'I take something for it',
                    'D' => 'I hug somebody',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'What do you call cheese that isn’t yours?',
                'image' => 'cheese.png',
                'answers' => [
                    'A' => 'Cheezy!',
                    'B' => 'Choosee!',
                    'C' => 'Nacho cheese!',
                    'D' => 'Gorgonzola!',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'Why did the tomato blush?',
                'image' => 'toma.png',
                'answers' => [
                    'A' => 'Because it saw the salad dressing',
                    'B' => 'Because he wanted to',
                    'C' => 'Just like that ',
                    'D' => 'Not the correct answer',
                ],
                'correct' => 'A',
            ],
            [
                'question' => 'Why do French people eat snails?',
                'image' => 'snail.png',
                'answers' => [
                    'A' => 'Because it\'s cheap',
                    'B' => 'Because it matches baguette',
                    'C' => 'Why not?',
                    'D' => 'Because they won\'t touch fast food',
                ],
                'correct' => 'D',
            ],
            [
                'question' => 'I went to buy some camouflage trousers the other day but...',
                'image' => 'camu.png',
                'answers' => [
                    'A' => 'They sold it out',
                    'B' => 'I couldn\'t find any',
                    'C' => 'They were not my size',
                    'D' => 'I have lost shop address',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'When life gives you melons....',
                'image' => 'lemo.jpg',
                'answers' => [
                    'A' => 'you give it back',
                    'B' => 'you do juice',
                    'C' => 'you are dyslexic',
                    'D' => 'you sell them',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'My friend farted in an elevator, it was wrong',
                'image' => 'elev.png',
                'answers' => [
                    'A' => 'on so many levels',
                    'B' => 'because he was not alone',
                    'C' => 'because there was no ventilation',
                    'D' => 'oops!',
                ],
                'correct' => 'A',
            ],
            [
                'question' => 'Why was six nervous?',
                'image' => 'sixx.png',
                'answers' => [
                    'A' => 'Because seven got lost',
                    'B' => 'Because seven met zero',
                    'C' => 'Because seven eight nine',
                    'D' => 'Because seven cried',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'What washes up on tiny beaches?',
                'image' => 'wave.png',
                'answers' => [
                    'A' => 'Microwaves',
                    'B' => 'Small waves',
                    'C' => 'Small people',
                    'D' => 'Microsoft',
                ],
                'correct' => 'A',
            ],
            [
                'question' => 'What do you call a tiny mother?',
                'image' => 'mini.png',
                'answers' => [
                    'A' => 'A mothee!',
                    'B' => 'A mommy!',
                    'C' => 'A mummy!',
                    'D' => 'A minimum!',
                ],
                'correct' => 'D',
            ],
            [
                'question' => 'Why are frogs so happy?',
                'image' => 'frog.png',
                'answers' => [
                    'A' => 'They have no problems, bro!',
                    'B' => 'They just are',
                    'C' => 'They eat whatever bugs them',
                    'D' => '¯\_(ツ)_/¯',
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
        if ($index > 0 && empty($question)) {
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
        $stats = [];

        if ($this->isAnswerRevealed()) {
            $stats = $this->getCurrentQuestionStats();
        }else {
            unset($question['correct']);
        }

        return [
            'question' => $question,
            'stats' => $stats,
        ];
    }

    private function isAnswerRevealed()
    {
        $result = DB::select("SELECT value FROM state WHERE id = 'SHOWANSWER'");
        return (int) $result[0]->value;
    }

    private function getCurrentQuestionIndex()
    {
        $result = DB::select("SELECT value FROM state WHERE id = 'STEP'");
        return (int) $result[0]->value;
    }

    private function getCurrentStartTime()
    {
        $result = DB::select("SELECT value FROM state WHERE id = 'STEPSTARTTIME'");
        return (int) $result[0]->value;
    }

    private function getCurrentQuestionStats()
    {
        $stats = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
        ];

        $statsPre = DB::select("
            SELECT answer, COUNT(*) AS answer_count
            FROM answers
            WHERE question = (SELECT value FROM state WHERE id='STEP')
            GROUP BY answer
        ");

        foreach ($statsPre as $statPre) {
            $stats[$statPre->answer] = (int) $statPre->answer_count;
        }

        return $stats;
    }

    public function saveAnswer(Request $request)
    {
        $answer = $request->input('answer');
        $index = $this->getCurrentQuestionIndex();
        $startTime = $this->getCurrentStartTime();
        $now = time();

        $question = $this->getCurrentFull();

        $points = 0;

        if ($question['correct'] === $answer && ($startTime+self::TIME_PER_QUESTION > $now)) {
            $points += self::POINTS_CORRECT;
            $points += 2 * ($startTime + self::TIME_PER_QUESTION - $now);
        }

        DB::insert('INSERT INTO answers (nick, question, answer, points, time) VALUES (?,?, ?, ?, ?)',
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
