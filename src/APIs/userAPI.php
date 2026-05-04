<?php
namespace App\APIs;

require_once "../../vendor/autoload.php";

use App\Helpers\Database;
use App\Models\UserModel;
use App\Controllers\UserController;

class UserAPI {
    private $userController;
    private $userModel;

    public function __construct() {
        $database = Database::getInstance();
        $this->userModel = new UserModel($database);
        $this->userController = new UserController($this->userModel);
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $action = $_GET['action'] ?? 'check-username';
        
        switch ($action) {
            case 'check-username':
                $this->checkUsername();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
        }
    }

    private function checkUsername() {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        
        $validationResult = $this->userController->validateUsernameOnly($username);
        
        if (!$validationResult['valid']) {
            echo json_encode([
                'available' => false,
                'valid' => false,
                'errors' => $validationResult['errors']
            ]);
            return;
        }

        $isAvailable = $this->userModel->checkUsernameAvailability($validationResult['username']);
        
        echo json_encode([
            'available' => $isAvailable,
            'valid' => true,
            'username' => $validationResult['username'],
            'message' => $isAvailable ? 'Username is available' : 'Username is already taken'
        ]);
    }
}

// Handle the request
$api = new UserAPI();
$api->handleRequest();
?>