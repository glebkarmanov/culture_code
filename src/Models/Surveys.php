<?php

namespace Src\Models;

class Surveys
{
    public function getSurvey($pdo ,$id)
    {
        $stmt = $pdo->prepare("
            SELECT sq.id AS question_id, sq.question_text, sq.scale_type, json_agg(json_build_object('option_id', so.id, 'option_text', so.option_text, 'option_value', so.option_value)) AS options
            FROM survey_questions sq
            JOIN survey_options so ON so.question_id = sq.id
            WHERE sq.survey_id = :survey_id
            GROUP BY sq.id, sq.question_text, sq.scale_type
            ORDER BY sq.id;
            ");
        $stmt->execute([
            'survey_id' => $id
        ]);
        $res = $stmt->fetchAll();
        return $res;
    }
}