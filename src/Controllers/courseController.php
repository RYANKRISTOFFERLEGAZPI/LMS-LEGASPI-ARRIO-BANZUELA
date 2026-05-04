<?php
namespace App\Controllers;

use App\Models\CourseModel;

class CourseController {
    private $courseModel;
    private $db;

    public function __construct($db) {
        $this->courseModel = new CourseModel($db);
        $this->db = $db;
    }

    public function createClass(array $input) {

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

    public function getCourses() {
        return $this->courseModel->getAllCourses();
    }

    public function getJoinedCourses($input) {
        return $this->courseModel->getCoursesEnrolledByUser($input);
    }

    public function joinClass($code, $userId) {
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

    public function deleteCourse($id) {
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