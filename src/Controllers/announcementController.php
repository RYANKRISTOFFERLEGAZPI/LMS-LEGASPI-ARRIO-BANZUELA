<?php
namespace App\Controllers;

use App\Models\AnnouncementModel;
use App\Helpers\Database;
use PDOException;

/**
 * Handles announcement-related operations such as creating
 * and managing course announcements.
 *
 * This controller validates input data and coordinates with the
 * AnnouncementModel to store announcements in the database.
 *
 * It ensures that only valid announcements are posted and that
 * required fields are properly validated before insertion.
 *
 * @author Ryan Kristoffer Legaspi, Ronnliegh Arrio, and Kit Banzuela
 * @since 2026-05-17
 * @package App\Controllers
 */
class AnnouncementController {

    /**
     * Announcement model instance used for database operations.
     *
     * @var AnnouncementModel
     */
    private $announcementModel;

    /**
     * Initializes the AnnouncementController with required dependencies.
     * This sets up the announcement model for handling announcement-related logic.
     *
     * @param Database $db Instance of the database helper
     */
    public function __construct(Database $db) {
        $this->announcementModel = new AnnouncementModel($db);
    }

    /**
     * Creates a new announcement for a course.
     * This method validates the announcement content and course ID,
     * then delegates the insertion to the AnnouncementModel.
     *
     * It ensures that announcements are not empty and are associated
     * with a valid course before being stored.
     *
     * @param array $input Associative array containing:
     *                     - course_id (int)
     *                     - course_name (string)
     *                     - content (string)
     * @param int $userId The ID of the user creating the announcement
     * @return array Returns:
     *               - success (bool)
     *               - message (string)
     *               - errors (array) if validation fails
     * @throws PDOException If the query execution fails
     */
    public function createAnnouncement(array $input, int $userId): array {

        $courseId = $input['course_id'] ?? 0;
        $content = trim($input['content'] ?? '');
        $courseName = $input['course_name'] ?? '';

        $errors = [];

        if ($content === '') {
            $errors[] = 'Announcement content is required';
        }

        if ($courseId <= 0) {
            $errors[] = 'Invalid course';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        $this->announcementModel->create([
            'course_id' => $courseId,
            'course_name' => $courseName,
            'content' => $content,
            'created_by' => $userId
        ]);

        return [
            'success' => true,
            'message' => 'Announcement posted successfully'
        ];
    }
}
?>