<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\CourseModel;
use App\Helpers\Database;
use PDOException;

/**
 * Handles course-related operations such as creation,
 * retrieval, enrollment, and deletion of courses.
 *
 * This controller validates user input, ensures proper business logic,
 * and delegates database operations to the CourseModel.
 *
 * It ensures that course management actions are performed securely
 * and consistently within the system.
 *
 * @author Ryan Kristoffer Legaspi, Ronnliegh Arrio, and Kit Banzuela
 * @since 2026-05-17
 * @package App\Controllers
 */
class CourseController {

    /**
     * Course model instance used for course-related database operations.
     *
     * @var CourseModel
     */
    private $courseModel;

    /**
     * Database helper instance used for accessing the database connection.
     *
     * @var Database
     */
    private $db;

    /**
     * Initializes the CourseController with required dependencies.
     * This sets up the course model and database connection
     * for handling course-related operations.
     *
     * @param Database $db Instance of the database helper
     */
    public function __construct(Database $db) {
        $this->courseModel = new CourseModel($db);
        $this->db = $db;
    }

    /**
     * Creates a new class (course).
     * This method validates input data, generates a unique course code,
     * and stores the course in the database.
     *
     * It ensures that only valid course data is saved.
     *
     * @param array $input Associative array containing:
     *                     - name (string): Course name
     *                     - section (string): Course section
     * @return array Returns:
     *               - success (bool)
     *               - message (string)
     *               - courseId (int) if successful
     *               - errors (array) if validation fails
     * @throws PDOException If the query execution fails
     */
    public function createClass(array $input): array {

        $name = trim($input['name'] ?? '');
        $section = trim($input['section'] ?? '');

        $errors = [];

        if ($name === '') {
            $errors[] = 'Class name is required';
        }

        if ($section === '') {
            $errors[] = 'Section is required';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        $code = $this->courseModel->generateCode();

        $courseId = $this->courseModel->create([
            'name' => $name,
            'section' => $section,
            'code' => $code
        ]);

        return [
            'success' => true,
            'message' => "Class created successfully! Code: $code",
            'courseId' => $courseId
        ];
    }

    /**
     * Retrieves all available courses.
     * This method is used to display a list of courses
     * within the system.
     *
     * @return array List of all courses
     * @throws PDOException If the query execution fails
     */
    public function getCourses(): array {
        return $this->courseModel->getAllCourses();
    }

    /**
     * Retrieves courses joined by a specific user.
     * This method delegates the retrieval to the CourseModel
     * to maintain separation of concerns.
     *
     * @param int $userId The ID of the user
     * @return array List of courses the user is enrolled in
     * @throws PDOException If the query execution fails
     */
    public function getJoinedCourses(int $userId): array {
        return $this->courseModel->getCoursesEnrolledByUser($userId);
    }

    /**
     * Allows a user to join a class using a course code.
     * This method validates input, checks if the course exists,
     * and ensures the user is not already enrolled before enrolling them.
     *
     * @param string $code The course code
     * @param int $userId The ID of the user joining the course
     * @return array Returns:
     *               - success (bool)
     *               - message (string)
     *               - errors (array) if validation fails
     * @throws PDOException If the query execution fails
     */
    public function joinClass(string $code, int $userId): array {

        $errors = [];

        if ($code === '') {
            $errors[] = 'Class code is required';
        }

        if ($userId <= 0) {
            $errors[] = 'User ID is required';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        $course = $this->courseModel->getCourseByCode($code);

        if (!$course) {
            return [
                'success' => false,
                'errors' => ['Invalid class code']
            ];
        }

        $courseId = $course['id'];

        if ($this->courseModel->isEnrolled($courseId, $userId)) {
            return [
                'success' => false,
                'errors' => ['You are already joined to this class']
            ];
        }

        $this->courseModel->enroll($courseId, $userId);

        return [
            'success' => true,
            'message' => 'Joined class successfully'
        ];
    }

    /**
     * Deletes a course.
     * This method ensures the course exists before deletion
     * and removes it along with related enrollments.
     *
     * @param int $id The course ID
     * @return array Returns:
     *               - success (bool)
     *               - message (string)
     *               - errors (array) if validation fails
     * @throws PDOException If the query execution fails
     */
    public function deleteCourse(int $id): array {

        $errors = [];

        if ($id <= 0) {
            $errors[] = 'Invalid course ID';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        $course = $this->courseModel->getById($id);

        if (!$course) {
            return [
                'success' => false,
                'errors' => ['Course not found']
            ];
        }

        $this->courseModel->delete($id);

        return [
            'success' => true,
            'message' => 'Course deleted successfully'
        ];
    }
}
?>