<?php

namespace Src\Controllers\Api;

use Src\Services\SurveysService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SurveysController
{
    public function getSurvey()
    {
        $request = Request::createFromGlobals();

        // Извлекаем параметр id из query-параметров (например, ?id=uuid)
        $survey_id = $request->query->get('survey_id');

        $service = new SurveysService();
        try {
            $result = $service->getInfoSurvey($survey_id);
            $responseData = json_encode($result);
            $response = new Response($responseData, 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $ex) {
            $status = $ex->getCode();
            $responseData = json_encode(['error' => $ex->getMessage()]);
            $response = new Response($responseData, $status, ['Content-Type' => 'application/json']);
        }

        $response->send();
        exit;
    }
}