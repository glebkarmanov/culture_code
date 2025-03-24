<?php

namespace Src\Services;

use PHPMailer\PHPMailer\Exception;
use Src\Models\Authentication;
use Src\Models\Companies;
use Src\Config\Database;

class CompaniesService
{
    public function getInfoCompanies($id):array
    {
        if (empty($id)) {
            throw new \Exception('Missing required fields', 400);
        }
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            throw new \Exception('Invalid id format', 400);
        }

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if (!$row) {
            throw new \Exception('Not Found', 404);
        }

        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'created_at' => $row['created_at']
        ];
    }

    public function changeNameAndEmailCompany($data)
    {
        if (empty($data['id'])) {
            throw new \Exception('Not found', 404);
        }
        if (empty($data['name']) && empty($data['email'])) {
            throw new \Exception('Missing required fields', 404);
        }

        if (!empty($data['email']))
        {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email format', 404);
            }

            $db = new Database();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("SELECT id FROM companies WHERE email = :email");
            $stmt->execute(['email' => $data['email']]);
            if ($stmt->fetch()) {
                throw new \Exception('Email already in use', 409);
            } else {
                $serviceCompanies = new Companies();
                $newEmail = $serviceCompanies->updateEmail($pdo, $data);
            }
        }


        if (!empty($data['name']))
        {
            $db = new Database();
            $pdo = $db->getConnection();
            $serviceCompanies = new Companies();
            $newName = $serviceCompanies->updateName($pdo, $data);
        }

        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->execute(['id' => $data['id']]);
        $row = $stmt->fetch();

        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'created_at' => date('c')
        ];
    }

    public function getMembersCompany($company_id): array
    {
        if (empty($company_id)) {
            throw new \Exception('Missing required fields', 400);
        }
        if (!filter_var($company_id, FILTER_VALIDATE_INT)) {
            throw new \Exception('Invalid id format', 400);
        }

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM company_members WHERE company_id = :company_id");
        $stmt->execute(['company_id' => $company_id]);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            throw new \Exception('Not Found', 404);
        }

        $members = [];
        foreach ($rows as $row) {
            $members[] = [
                'member_id'  => $row['id'],
                'name'       => $row['name'],
                'email'      => $row['email'],
                'team_id'    => $row['team_id'],
                'status'     => $row['status'],
                'created_at' => $row['created_at']
            ];
        }

        return [
            'company_id' => $company_id,
            'members'    => $members
        ];
    }

    public function addMemberCompany($data)
    {
        if (empty($data['name']) || empty($data['email']) || empty($data['company_id'])) {
            throw new \Exception('Missing required fields', 400);
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format', 400);
        }
        if (empty($data['team_id'])) {
            $data['team_id'] = null;
        }
        $data['status'] = "invited";

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->execute(['id' => $data['company_id']]);

        $rowCompanies = $stmt->fetch();
        if (!$rowCompanies) {
            throw new \Exception('Not found', 404);
        }

        $checkEmail = $pdo->prepare("SELECT * FROM company_members WHERE email = :email");
        $checkEmail->execute(['email' => $data['email']]);

        $rowMembers = $checkEmail->fetch();
        if ($rowMembers) {
            throw new \Exception('Email already in use', 409);
        }

        // Создание и сохранение записи компании
        $company = new Companies();
        $company->setName($data['name']);
        $company->setEmail($data['email']);
        $company->setCompanyId($data['company_id']);
        $company->setStatus($data['status']);
        $company->setTeamId($data['team_id']);
        $newId = $company->saveMember($pdo);


        return [
            'id' => $newId,
            'name' => $data['name'],
            'email' => $data['email'],
            'company_id' => $data['company_id'],
            'team_id' => $data['team_id'],
            'status' => $data['status'],
            'created_at' => date('c')
        ];
    }

    public function getInfoMember($id):array
    {
        if (empty($id)) {
            throw new \Exception('Missing required fields', 400);
        }
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            throw new \Exception('Invalid id format', 400);
        }

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM company_members WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if (!$row) {
            throw new \Exception('Not Found', 404);
        }

        return [
            'member_id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'company_id' => $row['company_id'],
            'team_id' => $row['team_id'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }

    public function updateInfoMember(array $data)
    {
        if (count($data) <= 1) {
            throw new \Exception('Missing required fields', 400);
        }

        $id = $data['member_id'];
        $currentUser = $this->getInfoMember($id);

        $newUser = array_merge($currentUser, $data);

        $db = new Database();
        $pdo = $db->getConnection();

        $companiesModel = new Companies();
        $companiesModel->updateMemberCompanyDb($pdo, $newUser);

        return [
            'member_id' => $newUser['member_id'],
            'name' => $newUser['name'],
            'email' => $newUser['email'],
            'company_id' => $newUser['company_id'],
            'team_id' => $newUser['team_id'],
            'status' => $newUser['status'],
            'created_at' => $newUser['created_at']
        ];
    }

    public function deleteForMember($id)
    {
        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM company_members WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if (!$row) {
            throw new \Exception('Not Found', 404);
        }

        $companiesModel = new Companies();
        $companiesModel->deleteMemberCompanyDb($pdo, $id);

        return "Участник {$row['name']} удален";
    }
}
