<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//TODO This class probably should be renamed to something like PlayerScreen
class Question extends Controller
{
    private const TIME_PER_QUESTION = 60;
    private const POINTS_CORRECT = 200;

    public function list(?int $index = null) : array
    {
        //TODO Move to config file
        $questions = [
            [
                'type' => 'abcd',
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
                'type' => 'point',
                'question' => 'Find the x',
                'image' => 'file.webp',
                'image_width' => 1196,
                'image_height' => 700,
                'correct_width' => 257,
                'correct_height' => 152,
                'correct_radius' => 145,
            ],
            [
                'type' => 'point',
                'question' => 'Find the YYYYY',
                'image' => 'file.webp',
                'image_width' => 1196,
                'image_height' => 700,
                'correct_width' => 257,
                'correct_height' => 152,
                'correct_radius' => 145,
            ],
            [
                'type' => 'point',
                'question' => 'Find the UUUUU',
                'image' => 'file.webp',
                'image_width' => 1196,
                'image_height' => 700,
                'correct_width' => 257,
                'correct_height' => 152,
                'correct_radius' => 145,
            ],
            [
                'type' => 'abcd',
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
        ];

        $questions = array_merge([[]], $questions);

        if ($index!== null) {
            if (isset($questions[$index])) {
                return $questions[$index];
            } else {
                return [];
            }
        }

        return $questions;

    }

    private function getCurrentFull(?Request $request) : array
    {
        $index = $this->getCurrentQuestionIndex();
        $question = $this->list($index);
        if ($index > 0 && empty($question)) {
            $question['id'] = '-1'; // TODO magic value
        } else {
            $question['id'] = $index;
        }
        $question['time_per_question'] = self::TIME_PER_QUESTION;

        if ($request) {
            $question['nick'] = $request->session()->get('nick');
        }

        return $question;
    }

    public function getCurrent(Request $request) : array
    {
        $question = $this->getCurrentFull($request);
        $question['revealed'] = false;
        $stats = [];
        $playersPoints = [];
        $nick = $request->session()->get('nick');

        if ($this->isAnswerRevealed()) {
            if (isset($question['type'])) {
                $question['revealed'] = true;

                if ($question['type'] === 'point'){
                    $playersPoints = $this->getCurrentQuestionChosenPoints($nick);
                } else {
                    $stats = $this->getCurrentQuestionStats();
                }
            }
        } else {
            unset($question['correct']);
            unset($question['correct_width']);
            unset($question['correct_height']);
            unset($question['correct_radius']);
        }

        return [
            'question' => $question,
            'stats' => $stats,
            'playersPoints' => $playersPoints,
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

    private function getCurrentQuestionChosenPoints($nick)
    {
        $points = [];
        $chosenPoints = DB::select("
            SELECT answer
            FROM answers
            WHERE question = (SELECT value FROM state WHERE id='STEP')
            AND nick != '?'
        ", [$nick]);

        //TODO ^ removing own result not working

        foreach ($chosenPoints as $chosenPoint) {
            $pair = explode(',', $chosenPoint->answer);
            $points[] = [
                (int) $pair[0],
                (int) $pair[1],
            ];
        }

        return $points;
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
        $question = $this->getCurrentFull($request);
        $startTime = $this->getCurrentStartTime();
        $now = time();
        $type = $request->input('type');
        $points = 0;
        $answer = '';

        if (($startTime + self::TIME_PER_QUESTION > $now)) {
            switch ($type) {
                case 'abcd':
                    $answer = $request->input('letter');
                    $points = $this->getAbcdPoints($request->input('letter'), $question);
                    break;
                case 'point':
                    $points = $this->getPointPoints(
                        (int) $request->input('image-width'),
                        (int) $request->input('image-height'),
                        (int) $request->input('click-x'),
                        (int) $request->input('click-y'),
                        $question
                    );

                    $answer = $this->getPointAnswer(
                        (int) $request->input('image-width'),
                        (int) $request->input('image-height'),
                        (int) $request->input('click-x'),
                        (int) $request->input('click-y'),
                        $question
                    );
                    $answer = implode(',', $answer);

                    break;
                default:
                    return 0;
            }

            if ($points > 0) {
                // Add bonus for quick answer
                $points += 2 * ($startTime + self::TIME_PER_QUESTION - $now);
            }
        }

        $index = $this->getCurrentQuestionIndex();

        DB::insert('INSERT INTO answers (nick, question, answer, points, time) VALUES (?,?, ?, ?, ?)',
            [
                $request->session()->get('nick'),
                $index,
                $answer,
                max(0, $points),
                $now
            ]);

        return $points;
    }

    private function getAbcdPoints(string $letter, array $question): int
    {
        $points = 0;
        if ($question['correct'] === $letter) {
            $points += self::POINTS_CORRECT;
        }

        return $points;
    }

    private function getPointPoints(int $clientImageWidth, int $clientImageHeight, int $clickX, int $clickY, array $question): int
    {
        $points = 0;

        $answerX = $this->convertClientDimensionToOriginal($question['image_width'], $clientImageWidth, $clickX);
        $answerY = $this->convertClientDimensionToOriginal($question['image_height'], $clientImageHeight, $clickY);

        $distanceFromCenter = floor($this->countDistance($question['correct_width'], $question['correct_height'], $answerX, $answerY));

        if ($distanceFromCenter <= ceil($question['correct_radius'])) {
            $points += self::POINTS_CORRECT;
        }

        return $points;
    }

    private function getPointAnswer(int $clientImageWidth, int $clientImageHeight, int $clickX, int $clickY, array $question): array
    {
        $answerX = $this->convertClientDimensionToOriginal($question['image_width'], $clientImageWidth, $clickX);
        $answerY = $this->convertClientDimensionToOriginal($question['image_height'], $clientImageHeight, $clickY);

        return [$answerX, $answerY];
    }

    private function convertClientDimensionToOriginal(int $maxOriginal, int $maxClient, int $clientValue): int
    {
        if ($maxClient === 0) {
            return 0;
        }

        return (int) (($clientValue * $maxOriginal)/$maxClient);
    }

    private function countDistance(int $x1, int $y1, int $x2, int $y2) {
        $a = $x1 - $x2;
        $b = $y1 - $y2;
        return sqrt($a*$a + $b*$b);
    }
}
