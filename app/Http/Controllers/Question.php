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
                'question' => 'Choose the fruit',
                'image' => 'fruit1.webp',
                'answers' => [
                    'A' => 'apple',
                    'B' => 'broccoli',
                    'C' => 'cabbage',
                    'D' => 'dinosaur',
                ],
                'correct' => 'A',
            ],
            [
                'type' => 'point',
                'question' => 'Choose the biggest cross',
                'image' => 'cross1.png',
                'image_width' => 600,
                'image_height' => 400,
                'correct_width' => 469,
                'correct_height' => 176,
                'correct_radius' => 85,
            ],
            [
                'type' => 'point',
                'question' => 'Click the yellow heart',
                'image' => 'heart1.png',
                'image_width' => 650,
                'image_height' => 400,
                'correct_width' => 579,
                'correct_height' => 343,
                'correct_radius' => 40,
            ],
            [
                'type' => 'abcd',
                'question' => 'ABCD question',
                'image' => 'fruit1.webp',
                'answers' => [
                    'A' => 'Bad',
                    'B' => 'Bad',
                    'C' => '-> Correct',
                    'D' => 'Bad',
                ],
                'correct' => 'C',
            ],
            [
                'type' => 'abcd',
                'question' => 'ABCD question too',
                'image' => 'fruit2.webp',
                'answers' => [
                    'A' => 'Bad',
                    'B' => 'Bad',
                    'C' => 'Bad',
                    'D' => '-> Correct',
                ],
                'correct' => 'D',
            ],
            [
                'type' => 'point',
                'question' => 'Click the brown dot in the circle',
                'image' => 'file.webp',
                'image_width' => 1196,
                'image_height' => 700,
                'correct_width' => 257,
                'correct_height' => 144,
                'correct_radius' => 150,
            ],
            [
                'type' => 'point',
                'question' => 'Click the yellow heart',
                'image' => 'heart1.png',
                'image_width' => 650,
                'image_height' => 400,
                'correct_width' => 579,
                'correct_height' => 343,
                'correct_radius' => 40,
            ],
            [
                'type' => 'point',
                'question' => 'Find the sun, not the flower',
                'image' => 'file.webp',
                'image_width' => 1196,
                'image_height' => 700,
                'correct_width' => 706,
                'correct_height' => 288,
                'correct_radius' => 100,
            ],
            [
                'type' => 'point',
                'question' => 'Click the top of this fruit mountain',
                'image' => 'fruit1.webp',
                'image_width' => 200,
                'image_height' => 200,
                'correct_width' => 112,
                'correct_height' => 38,
                'correct_radius' => 30,
            ],
        ];

        $questions = array_merge([[]], $questions);

        if ($index !== null) {
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
