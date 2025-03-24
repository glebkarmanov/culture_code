<?php

namespace Src\Controllers\Api;

use Src\Services\AuthenticationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController {
    public function register() {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $service = new AuthenticationService();
        try {
            $result = $service->registerCompany($data);
            $responseData = json_encode($result);
            $response = new Response($responseData, 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $ex) {
            $status = ($ex->getCode() === 409) ? 409 : 400;
            $responseData = json_encode(['error' => $ex->getMessage()]);
            $response = new Response($responseData, $status, ['Content-Type' => 'application/json']);
        }
        $response->send();
        exit;
    }

    public function signIn()
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $service = new AuthenticationService();

        try {
            $result = $service->signInCompany($data);
            $responseData = json_encode($result);
            $response = new Response($responseData, 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $ex) {
            $status = ($ex->getCode() === 401) ? 401 : 400;
            $responseData = json_encode(['error' => $ex->getMessage()]);
            $response = new Response($responseData, $status, ['Content-Type' => 'application/json']);
        }
        $response->send();
        exit;
    }
    public function forgotPassword() {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $service = new AuthenticationService();
        try {
            $result = $service->passwordResetSend($data);
            $response = new Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $ex) {
            $status = ($ex->getCode() === 401) ? 401 : 400;
            $response = new Response(json_encode(['error' => $ex->getMessage()]), $status, ['Content-Type' => 'application/json']);
        }
        $response->send();
        exit;
    }

}
