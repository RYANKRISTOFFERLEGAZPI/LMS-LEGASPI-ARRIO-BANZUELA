<?php
namespace App\Models;

class CourseModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function generateCode() {
        return strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    public function insertCourse($name, $section, $code) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("INSERT INTO courses (name, section, code) VALUES (?, ?, ?)");
        $stmt->execute([$name, $section, $code]);

        return $conn->lastInsertId();
    }

    public function getAllCourses() {
        $conn = $this->db->getConnection();

        $stmt = $conn->query("SELECT id, name, section, code FROM courses");

        return $stmt->fetchAll();
    }

    public function getCourseByCode($code) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT id, name, section, code FROM courses WHERE code = ?");
        $stmt->execute([$code]);

        return $stmt->fetch();
    }

    public function isEnrolled($courseId, $userId) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ? AND user_id = ?");
        $stmt->execute([$courseId, $userId]);

        $result = $stmt->fetch();
        return !empty($result) && $result['count'] > 0;
    }

    public function enroll($courseId, $userId) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("INSERT INTO enrollments (course_id, user_id) VALUES (?, ?)");
        $stmt->execute([$courseId, $userId]);

        return $conn->lastInsertId();
    }

    public function create(array $data) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("INSERT INTO courses (name, section, code) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $data['section'], $data['code']]);

        return $conn->lastInsertId();
    }

    public function getAll() {
        $conn = $this->db->getConnection();

        $stmt = $conn->query("SELECT id, name, section, code FROM courses");

        return $stmt->fetchAll();
    }

    public function getById($id) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT id, name, section, code FROM courses WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function getCoursesEnrolledByUser($userId) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetchAll();

        $stmt = $conn->prepare("SELECT name, section FROM courses WHERE id = ?");
        $courses = [];
        foreach ($result as $enrollment) {
            $stmt->execute([$enrollment['course_id']]);
            $courses[] = $stmt->fetch();
        }
        return $courses;
    }

    public function delete($id) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
        $stmt->execute([$id]);

        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount();
    }

}
?>