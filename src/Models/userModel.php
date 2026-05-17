<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Handles database operations related to user entities.
 * This class provides methods for checking username availability
 * and retrieving student records from the system.
 *
 * @author Ryan Kristoffer Legaspi, Ronnliegh Arrio, and Kit Banzuela
 * @since 2026-05-17
 * @package App\Models
 */
class UserModel {

    /**
     * Database connection instance used for executing queries.
     *
     * @var PDO
     */
    private $conn;

    /**
     * Initializes the UserModel with a database connection.
     * This constructor ensures that all methods in this class
     * can interact with the database.
     *
     * @param $database Database helper instance that provides a PDO connection
     * @throws PDOException If connection retrieval fails
     */
    public function __construct($database) {
        $this->conn = $database->getConnection();
    }


    /**
     * Retrieves all users with a student role.
     * This method is used to display or manage student records
     * within the system.
     *
     * @return array Returns an array of student records (id, username, full_name)
     * @throws PDOException If the query execution fails
     */
    public function getStudents(): array {
        $sql = "SELECT id, username, full_name FROM users WHERE type = 'student'";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
}
?>