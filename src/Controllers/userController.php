<?php
namespace App\Controllers;
use App\Models\UserModel;

class UserController {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
        $this->db = $db;
    }

    public function loginValidate($input) {

        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            return [
                'success' => false,
                'errors' => ['Username and password are required']
            ];
        }

        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT id, username, password, type FROM users WHERE username = ?");

        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'errors' => ['Invalid username or password']
            ];
        }

        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'errors' => ['Invalid username or password']
            ];
        }

        unset($user['password']);

        return [
            'success' => true,
            'data' => $user
        ];
    }

    public function register(array $input) {
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $confirmPassword = $input['confirm_password'] ?? '';
        $email = $input['email'] ?? '';
        $type = $input['type'] ?? 'guest';

        $errors = [];

        if ($username === '') {
            $errors[] = 'Username is required';
        }
        if ($email === '') {
            $errors[] = 'Email is required';
        }
        if ($firstName === '') {
            $errors[] = 'First name is required';
        }
        if ($lastName === '') {
            $errors[] = 'Last name is required';
        }
        if ($password === '') {
            $errors[] = 'Password is required';
        }
        if ($confirmPassword === '') {
            $errors[] = 'Confirm password is required';
        }
        if ($password !== '' && $confirmPassword !== '' && $password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            return [
                'success' => false,
                'errors' => ['Username is already taken']
            ];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, type) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$username, $passwordHash, $firstName, $lastName, $email, $type]);

        return [
            'success' => true,
            'message' => 'Registration completed successfully. You can now login.'
        ];
    }

    public function getStudents() {
        return $this->userModel->getStudents();
    }
}
