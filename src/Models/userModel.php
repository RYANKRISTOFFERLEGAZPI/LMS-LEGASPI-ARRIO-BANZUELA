<?php
namespace App\Models;

class UserModel{

    private $conn;

    public function __construct($database) {
        $this->conn = $database->getConnection();
    }
    
    public function checkUsernameAvailability($username) {
        $sql = "SELECT id FROM account WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result === false;
    }

    public function getStudents() {
        $sql = "SELECT id, username, full_name FROM users WHERE type = 'student'";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
}
?>