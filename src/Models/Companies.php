<?php

namespace Src\Models;

class Companies
{
    private $id;
    private $name;
    private $email;
    private $company_id;
    private $status;
    private $team_id;

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setEmail(string $email) {
        $this->email = $email;
    }

    public function setCompanyId(string $company_id) {
        $this->company_id = $company_id;
    }

    public function setStatus(string $status) {
        $this->status = $status;
    }

    public function setTeamId($team_id) {
        $this->team_id = $team_id;
    }

    public function saveMember($pdo): string {
        $stmt = $pdo->prepare("
            INSERT INTO company_members (company_id, team_id, name, email, status)
            VALUES (:company_id, :team_id, :name, :email, :status)
            RETURNING id
        ");
        $stmt->execute([
            'name' => $this->name,
            'email' => $this->email,
            'company_id' => $this->company_id,
            'team_id' => $this->team_id,
            'status' => $this->status
        ]);
        $row = $stmt->fetch();
        return $row['id'];
    }

    public function updateMemberCompanyDb($pdo, $data)
    {
        $stmt = $pdo->prepare("
            UPDATE company_members
            SET company_id = :company_id, team_id = :team_id, name = :name, email = :email, status = :status
            WHERE id = :id
        ");
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'company_id' => $data['company_id'],
            'team_id' => $data['team_id'],
            'status' => $data['status'],
            'id' => $data['member_id']
        ]);
    }

    public function deleteMemberCompanyDb($pdo, $id)
    {
        $stmt = $pdo->prepare("
            DELETE from company_members
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $id
        ]);
    }

    public function updateName($pdo, $data)
    {
        $stmt = $pdo->prepare("
            UPDATE companies
            SET name = :name
            WHERE id = :id
        ");
        $stmt->execute([
            'name' => $data['name'],
            'id' => $data['id']
        ]);
    }

    public function updateEmail($pdo, $data)
    {
        $stmt = $pdo->prepare("
            UPDATE companies
            SET email = :email
            WHERE id = :id
        ");
        $stmt->execute([
            'email' => $data['email'],
            'id' => $data['id']
        ]);
    }
}