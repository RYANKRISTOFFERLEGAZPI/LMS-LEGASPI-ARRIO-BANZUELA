<?php
declare(strict_types=1);
namespace App\Models;

use PDO;
use PDOException;
use App\Helpers\Database;

/**
 * Handles database operations related to student entities.
 * This class is responsible for retrieving student records,
 * particularly in relation to course enrollments.
 *
 * It ensures that only users with a student role are fetched
 * when querying course-related data.
 *
 * @author Ryan Kristoffer Legaspi, Ronnliegh Arrio, and Kit Banzuela
 * @since 2026-05-17
 * @package App\Models
 */
class StudentModel {

    /**
     * Database connection instance used for executing queries.
     *
     * @var PDO
     */
    private $conn;

    /**
     * Initializes the StudentModel with a database connection.
     * This ensures consistent database access for all student-related queries.
     *
     * @param Database $database Instance of the database helper providing a PDO connection
     * @throws PDOException If connection retrieval fails
     */
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }

    /**
     * Retrieves all students enrolled in a specific course.
     * This method joins the enrollments and users tables to ensure
     * that only users with a student role are returned for the given course.
     *
     * @param int $courseId The ID of the course to retrieve enrolled students from
     * @return array Returns an array of enrolled students (id, full_name, username)
     * @throws PDOException If the query execution fails
     */
    public function getStudentsByCourse(int $courseId): array {
        $sql = "
            SELECT 
                users.id,
                users.full_name,
                users.username
            FROM enrollments
            INNER JOIN users
                ON users.id = enrollments.user_id
            WHERE enrollments.course_id = :course_id
            AND users.type = 'student'
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':course_id', $courseId);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
?>