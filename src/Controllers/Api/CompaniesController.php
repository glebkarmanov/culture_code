<?php

namespace Src\Controllers\Api;

use Src\Services\CompaniesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompaniesController
{
    public function getInfo()
    {
        // Создаем объект запроса из глобальных переменных
        $request = Request::createFromGlobals();

        // Извлекаем параметр id из query-параметров (например, ?id=uuid)
        $id = $request->query->get('id');

        $service = new CompaniesService();
        try {
            $result = $service->getInfoCompanies($id);
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

    public function changeNameAndEmail()
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $service = new CompaniesService();
        try {
            $result = $service->changeNameAndEmailCompany($data);
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


    public function getMembers()
    {
        $request = Request::createFromGlobals();
        $id = $request->query->get('company_id');

        $service = new CompaniesService();
        try {
            $result = $service->getMembersCompany($id);
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
    public function addMember()
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $service = new CompaniesService();
        try {
            $result = $service->addMemberCompany($data);
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

    public function getMember()
    {
        $request = Request::createFromGlobals();
        $id = $request->query->get('member_id');

        $service = new CompaniesService();
        try {
            $result = $service->getInfoMember($id);
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

    public function updateMember()
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $service = new CompaniesService();
        try {
            $result = $service->updateInfoMember($data);
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

    public function deleteMember()
    {
        $request = Request::createFromGlobals();
        $id = $request->query->get('member_id');

        $service = new CompaniesService();
        try {
            $result = $service->deleteForMember($id);
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