<?php
namespace App\Models;

use PDO;
use PDOException;
use App\Helpers\Database;

/**
 * Handles database operations related to announcement entities.
 * This class is responsible for creating and retrieving announcements
 * associated with courses, ensuring proper communication between faculty
 * and students within the system.
 *
 * It centralizes announcement-related data access to maintain
 * separation of concerns in the application.
 *
 * @author Ryan Kristoffer Legaspi, Ronnliegh Arrio, and Kit Banzuela
 * @since 2026-05-17
 * @package App\Models
 */
class AnnouncementModel {

    /**
     * Database connection instance used for executing queries.
     *
     * @var PDO
     */
    private $conn;

    /**
     * Initializes the AnnouncementModel with a database connection.
     * This ensures all announcement-related operations use a consistent
     * and reusable database connection.
     *
     * @param Database $db Instance of the database helper providing a PDO connection
     * @throws PDOException If connection retrieval fails
     */
    public function __construct(Database $db) {
        $this->conn = $db->getConnection();
    }

    /**
     * Creates a new announcement.
     * This method inserts announcement data into the database,
     * allowing faculty to post updates for a specific course.
     *
     * @param array $data Associative array containing:
     *                    - course_id (int): ID of the course
     *                    - course_name (string): Name of the course
     *                    - content (string): Announcement content
     *                    - created_by (int): ID of the creator (faculty)
     * @return int The ID of the newly created announcement
     * @throws PDOException If the query execution fails
     */
    public function create(array $data): int {
        $stmt = $this->conn->prepare("
            INSERT INTO announcements (course_id, course_name, content, created_by)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['course_id'],
            $data['course_name'],
            $data['content'],
            $data['created_by']
        ]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Retrieves all announcements from the database.
     * This method is typically used to display announcements
     * across courses or within dashboards.
     *
     * @return array List of all announcements
     * @throws PDOException If the query execution fails
     */
    public function getAnnouncements(): array {
        $stmt = $this->conn->query("SELECT * FROM announcements");
        return $stmt->fetchAll();
    }
}
?>