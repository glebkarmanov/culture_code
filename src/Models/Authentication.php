<?php

namespace Src\Models;

class Authentication {
    private $id;
    private $name;
    private $email;
    private $password;

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setEmail(string $email) {
        $this->email = $email;
    }

    public function setPassword(string $password) {
        $this->password = $password;
    }

    public function save($pdo): string {
        $stmt = $pdo->prepare("
            INSERT INTO companies (name, email, password)
            VALUES (:name, :email, :password)
            RETURNING id
        ");
        $stmt->execute([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password
        ]);
        $row = $stmt->fetch();
        return $row['id'];
    }
}
