<?php

namespace Src\Services;

use Src\Models\Authentication;
use Src\Config\Database;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Dotenv\Dotenv;

class AuthenticationService {
    public function registerCompany(array $data): array {
        // Валидация обязательных полей
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new \Exception('Missing required fields');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format');
        }

        // Проверка уникальности email
        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);
        if ($stmt->fetch()) {
            throw new \Exception('Email already in use', 409);
        }

        // Хэширование пароля
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        // Создание и сохранение записи компании
        $company = new Authentication();
        $company->setName($data['name']);
        $company->setEmail($data['email']);
        $company->setPassword($passwordHash);
        $newId = $company->save($pdo);

        // Генерация токена можно добавить здесь

        return [
            'id' => $newId,
            'name' => $data['name'],
            'email' => $data['email'],
            'created_at' => date('c')
        ];
    }

    public function signInCompany(array $data):array
    {
        // Валидация обязательных полей
        if (empty($data['email']) || empty($data['password'])) {
            throw new \Exception('Missing required fields');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format');
        }

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);

        $row = $stmt->fetch();
        $passwordHashDb = $row['password'];

        if (!$row) {
            throw new \Exception('Unauthorized', 401);
        }

        if (!password_verify($data['password'], $passwordHashDb)) {
            throw new \Exception('Unauthorized password', 401);
        }

        $secretKey = 'MySuperSecretKey2025!'; // Секретный ключ для подписи токена
        $userId = $row['id']; // Идентификатор пользователя или компании
        $issuedAt = time();

        $payload = [
            'iss' => 'CulturalCodeAPI',         // Издатель токена
            'aud' => 'CulturalCodeUsers',       // Аудитория
            'iat' => $issuedAt,                 // Время выпуска
            'sub' => $userId                    // Идентификатор субъекта (пользователя или компании)
        ];

        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        return [
            'token' => $jwt,
            'company' => [
                'id' => $userId,
                'name' => $row['name'],
                'email' => $row['email']
            ]
        ];
    }

    public function passwordResetSend($data)
    {
        if (empty($data['email'])) {
            throw new \Exception('Missing required fields', 400);
        }
        $email = trim($data['email']);
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format', 400);
        }

        $db = new Database();
        $pdo = $db->getConnection();
        $checkEmail = $pdo->prepare("SELECT * FROM companies WHERE email = :email");
        $checkEmail->execute(['email' => $data['email']]);
        $company = $checkEmail->fetch();

        if (!$company) {
            throw new \Exception('Email not found', 409);
        }

        // Генерация токена восстановления (например, действительного 1 час)
        $token = bin2hex(random_bytes(16)); // 32-символьный токен
        $expiration = date('Y-m-d H:i:s', time() + 3600);

        // Сохраняем токен в таблицу password_resets (предполагается, что такая таблица уже создана)
        $stmt = $pdo->prepare("
            INSERT INTO password_resets (company_id, token, expiration)
            VALUES (:company_id, :token, :expiration)
            ON CONFLICT (company_id) DO UPDATE SET token = :token, expiration = :expiration
        ");
        $stmt->execute([
            'company_id' => $company['id'],
            'token' => $token,
            'expiration' => $expiration,
        ]);

        // Формируем ссылку для сброса пароля (APP_URL задаётся в .env)
        $resetLink = ($_ENV['APP_URL'] ?? 'http://localhost') . '/reset-password?token=' . $token;

        // Отправляем email с инструкциями, используя PHPMailer
        $mail = new PHPMailer(true);
        try {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();


            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? 'gleb.karmanov.22@gmail.com';     // ваш полный адрес Gmail
            $mail->Password   = $_ENV['SMTP_PASS'] ?? '1Nocokooo';          // пароль приложения или обычный пароль (если без 2FA)
            $mail->SMTPSecure = 'tls';                        // или 'ssl' если используете порт 465
            $mail->Port       = 587;

            $mail->setFrom('gleb.karmanov.22@gmail.com', 'Cultural Code Support');
            $mail->addAddress($email, $company['name']);

            $mail->Subject = 'Инструкции по восстановлению пароля';
            $mail->Body    = "Здравствуйте, " . $company['name'] . "!\n\n" .
                "Для восстановления пароля перейдите по ссылке ниже. Ссылка действительна 1 час:\n\n" .
                $resetLink . "\n\n" .
                "Если вы не запрашивали восстановление пароля, проигнорируйте это сообщение.";

            $mail->send();
        } catch (PHPMailerException $e) {
            throw new \Exception('Failed to send reset email: ' . $e->getMessage(), 500);
        }

        return [
            'message' => 'На указанный email отправлены инструкции по восстановлению пароля.'
        ];
    }
}
