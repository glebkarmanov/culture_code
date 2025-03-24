<?php

namespace Src\Services;

use PHPMailer\PHPMailer\Exception;
use Src\Models\Surveys;
use Src\Config\Database;

class SurveysService
{
    public function getInfoSurvey($survey_id)
    {
        if (empty($survey_id)) {
            throw new \Exception('Missing required fields', 400);
        }
        if (!filter_var($survey_id, FILTER_VALIDATE_INT)) {
            throw new \Exception('Invalid survey_id format', 400);
        }

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = :id");
        $stmt->execute(['id' => $survey_id]);

        $row = $stmt->fetch();
        if (!$row) {
            throw new \Exception('Not Found ID', 404);
        }

        $teamsModel = new Surveys();
        $getSurvey = $teamsModel->getSurvey($pdo, $survey_id);

        if (!$getSurvey) {
            throw new \Exception('Not Found Questions', 404);
        }

        $questions = [];
        foreach ($getSurvey as $question) {
            $options = [];
            if (isset($question['options'])) {
                if (!is_array($question['options'])) {
                    $decoded = json_decode($question['options'], true);
                    $optionsArray = is_array($decoded) ? $decoded : [];
                } else {
                    $optionsArray = $question['options'];
                }
                foreach ($optionsArray as $option) {
                    $options[] = [
                        'option_id' => $option['option_id'],
                        'text'      => $option['option_text'],
                        'value'     => $option['option_value']
                    ];
                }
            }
            $questions[] = [
                'question_id' => $question['question_id'],
                'text'        => $question['question_text'],
                'scale_type'  => $question['scale_type'],
                'options'     => $options
            ];
        }

        return [
            'survey_id' => $row['id'],
            'title'     => $row['title'],
            'questions' => $questions
        ];
    }
}