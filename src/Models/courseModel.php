<?php
declare(strict_types=1);
namespace App\Models;

use PDO;
use PDOException;
use App\Helpers\Database;

/**
 * Handles database operations related to course entities.
 * This class manages course creation, retrieval, enrollment,
 * and deletion, ensuring proper handling of course-related data
 * within the system.
 *
 * It also provides helper methods for generating course codes
 * and checking user enrollment status.
 *
 * @author Ryan Kristoffer Legaspi, Ronnliegh Arrio, and Kit Banzuela
 * @since 2026-05-17
 * @package App\Models
 */
class CourseModel {

    /**
     * Database connection instance used for executing queries.
     *
     * @var PDO
     */
    private $conn;

    /**
     * Initializes the CourseModel with a database connection.
     * This ensures all course-related operations use a consistent
     * and reusable database connection.
     *
     * @param Database $db Instance of the database helper providing a PDO connection
     * @throws PDOException If connection retrieval fails
     */
    public function __construct(Database $db) {
        $this->conn = $db->getConnection();
    }

    /**
     * Generates a unique course code.
     * This is used to allow students to join a course securely.
     *
     * @return string A randomly generated 6-character uppercase course code
     * @throws \Exception If random byte generation fails
     */
    public function generateCode(): string {
        return strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    /**
     * Inserts a new course into the database.
     * This method is used when creating a course manually.
     *
     * @param string $name The name of the course
     * @param string $section The section identifier of the course
     * @param string $code The unique course code
     * @return int The ID of the newly created course
     * @throws PDOException If the query execution fails
     */
    public function insertCourse(string $name, string $section, string $code): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO courses (name, section, code) VALUES (?, ?, ?)"
        );
        $stmt->execute([$name, $section, $code]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Retrieves all courses from the database.
     * This is typically used for displaying course lists.
     *
     * @return array List of all courses
     * @throws PDOException If the query execution fails
     */
    public function getAllCourses(): array {
        $stmt = $this->conn->query(
            "SELECT id, name, section, code FROM courses"
        );
        return $stmt->fetchAll();
    }

    /**
     * Retrieves a course using its unique code.
     * This is used when a user joins a course via code.
     *
     * @param string $code The course code
     * @return array|false The course data or false if not found
     * @throws PDOException If the query execution fails
     */
    public function getCourseByCode(string $code) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, section, code FROM courses WHERE code = ?"
        );
        $stmt->execute([$code]);

        return $stmt->fetch();
    }

    /**
     * Checks if a user is already enrolled in a course.
     * This prevents duplicate enrollment entries.
     *
     * @param int $courseId The course ID
     * @param int $userId The user ID
     * @return bool True if enrolled, otherwise false
     * @throws PDOException If the query execution fails
     */
    public function isEnrolled(int $courseId, int $userId): bool {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM enrollments WHERE course_id = ? AND user_id = ?"
        );
        $stmt->execute([$courseId, $userId]);

        $result = $stmt->fetch();
        return !empty($result) && $result['count'] > 0;
    }

    /**
     * Enrolls a user into a course.
     * This method creates a new enrollment record.
     *
     * @param int $courseId The course ID
     * @param int $userId The user ID
     * @return int The ID of the created enrollment record
     * @throws PDOException If the query execution fails
     */
    public function enroll(int $courseId, int $userId): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO enrollments (course_id, user_id) VALUES (?, ?)"
        );
        $stmt->execute([$courseId, $userId]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Creates a new course using an associative array of data.
     * This provides a flexible way to insert course records.
     *
     * @param array $data Course data containing name, section, and code
     * @return int The ID of the newly created course
     * @throws PDOException If the query execution fails
     */
    public function create(array $data): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO courses (name, section, code) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $data['name'],
            $data['section'],
            $data['code']
        ]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Retrieves all courses (alias of getAllCourses).
     *
     * @return array List of all courses
     * @throws PDOException If the query execution fails
     */
    public function getAll(): array {
        return $this->getAllCourses();
    }

    /**
     * Retrieves a course by its ID.
     *
     * @param int $id The course ID
     * @return array|false The course data or false if not found
     * @throws PDOException If the query execution fails
     */
    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, section, code FROM courses WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    /**
     * Retrieves all courses a user is enrolled in.
     * This method maps enrollment records to actual course details.
     *
     * @param int $userId The user ID
     * @return array List of enrolled courses
     * @throws PDOException If the query execution fails
     */
    public function getCoursesEnrolledByUser(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT course_id FROM enrollments WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $enrollments = $stmt->fetchAll();

        $courses = [];
        $courseStmt = $this->conn->prepare(
            "SELECT id, name, section FROM courses WHERE id = ?"
        );

        foreach ($enrollments as $enrollment) {
            $courseStmt->execute([$enrollment['course_id']]);
            $courses[] = $courseStmt->fetch();
        }

        return $courses;
    }

    /**
     * Deletes a course and its related enrollments.
     * This ensures referential integrity by removing dependent records first.
     *
     * @param int $id The course ID
     * @return int Number of affected rows for the course deletion
     * @throws PDOException If the query execution fails
     */
    public function delete(int $id): int {
        $stmt = $this->conn->prepare(
            "DELETE FROM enrollments WHERE course_id = ?"
        );
        $stmt->execute([$id]);

        $stmt = $this->conn->prepare(
            "DELETE FROM courses WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->rowCount();
    }
}
?>