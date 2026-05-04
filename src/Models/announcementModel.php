<?php
namespace App\Models;

class AnnouncementModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("
            INSERT INTO announcements (course_id, content, created_by)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $data['course_id'],
            $data['content'],
            $data['created_by']
        ]);

        return $conn->lastInsertId();
    }

    public function getAnnouncements() {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM announcements");
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
?>