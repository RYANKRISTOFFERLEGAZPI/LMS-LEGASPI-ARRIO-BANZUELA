<?php
namespace App\APIs;

require_once "../../vendor/autoload.php";

use App\Helpers\Database;
use App\Models\UserModel;
use App\Controllers\UserController;

class UserAPI {
    private UserController $userController;
    private UserModel $userModel;

    public function __construct() {
        $database = Database::getInstance();
        $this->userModel = new UserModel($database);
        $this->userController = new UserController($this->userModel);
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        
        // Handle CORS if needed
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Get the action from the request
        $action = $_GET['action'] ?? 'check-username';
        
        switch ($action) {
            case 'check-username':
                $this->checkUsername();
                break;
        }
    }

    private function checkUsername() {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        
        // Use controller to validate
        $userValidationResult = $this->userController->validateUsername($username);
        
        if (!$userValidationResult['valid']) {
            echo json_encode([
                'available' => false,
                'valid' => false,
                'errors' => $userValidationResult['errors']
            ]);
            return;
        }

        // Check availability in database
        $isAvailable = $this->userModel->checkUsernameAvailability($userValidationResult['username']);
        
        echo json_encode([
            'available' => $isAvailable,
            'valid' => true,
            'username' => $userValidationResult['username'],
            'message' => $isAvailable ? 'Username is available' : 'Username is already taken'
        ]);
    }

}

// Handle the request
$api = new UserAPI();
$api->handleRequest();
?>