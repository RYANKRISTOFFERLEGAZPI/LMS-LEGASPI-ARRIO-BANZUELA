<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\UserModel;
use App\Helpers\Database;
use PDO;
use PDOException;

/**
 * Handles user-related operations such as authentication,
 * registration, and retrieval of user data.
 *
 * This controller validates user input, manages login sessions,
 * and coordinates with the UserModel for database interactions.
 *
 * It ensures secure handling of credentials and proper validation
 * before performing any user-related actions.
 *
 * @author Ryan Kristoffer Legaspi, Ronnliegh Arrio, and Kit Banzuela
 * @since 2026-05-17
 * @package App\Controllers
 */
class UserController {

    /**
     * Database helper instance used to access the PDO connection.
     *
     * @var Database
     */
    private $db;

    /**
     * User model instance used for user-related database operations.
     *
     * @var UserModel
     */
    private $userModel;

    /**
     * Initializes the UserController with required dependencies.
     * This sets up the database connection and user model
     * to handle user-related operations.
     *
     * @param Database $db Instance of the database helper
     */
    public function __construct(Database $db) {
        $this->userModel = new UserModel($db);
        $this->db = $db;
    }

    /**
     * Validates user login credentials.
     * This method checks if the provided username and password
     * match an existing user record and verifies the hashed password.
     *
     * It ensures that only authenticated users can access
     * protected parts of the system.
     *
     * @param array $input Associative array containing:
     *                     - username (string)
     *                     - password (string)
     * @return array Returns:
     *               - success (bool)
     *               - data (array) if successful
     *               - errors (array) if validation fails
     * @throws PDOException If the query execution fails
     */
    public function loginValidate(array $input): array {

        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            return [
                'success' => false,
                'errors' => ['Username and password are required']
            ];
        }

        $conn = $this->db->getConnection();

        $stmt = $conn->prepare(
            "SELECT id, username, password, type FROM users WHERE username = ?"
        );

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

        // Remove password before returning user data for security
        unset($user['password']);

        return [
            'success' => true,
            'data' => $user
        ];
    }

    /**
     * Registers a new user.
     * This method validates all required input fields, ensures that
     * the username is unique, and securely hashes the password
     * before storing it in the database.
     *
     * It prevents invalid or duplicate user data from being inserted.
     *
     * @param array $input Associative array containing:
     *                     - username (string)
     *                     - password (string)
     *                     - confirm_password (string)
     *                     - first_name (string)
     *                     - last_name (string)
     *                     - email (string)
     *                     - type (string)
     * @return array Returns:
     *               - success (bool)
     *               - message (string) if successful
     *               - errors (array) if validation fails
     * @throws PDOException If the query execution fails
     */
    public function register(array $input): array {

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

        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            return [
                'success' => false,
                'errors' => ['Username is already taken']
            ];
        }

        // Hash password before storing for security
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $conn->prepare(
            "INSERT INTO users (username, password, first_name, last_name, email, type)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $insert->execute([
            $username,
            $passwordHash,
            $firstName,
            $lastName,
            $email,
            $type
        ]);

        return [
            'success' => true,
            'message' => 'Registration completed successfully. You can now login.'
        ];
    }

    /**
     * Retrieves all student users.
     * This method delegates the retrieval process to the UserModel
     * to maintain separation between controller logic and data access.
     *
     * @return array List of student users
     * @throws PDOException If the query execution fails
     */
    public function getStudents(): array {
        return $this->userModel->getStudents();
    }
}