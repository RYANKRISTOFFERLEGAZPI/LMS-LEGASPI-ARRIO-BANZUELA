<?php
namespace App\Controllers;

use App\Models\AnnouncementModel;

class AnnouncementController {
    private $announcementModel;

    public function __construct($db) {
        $this->announcementModel = new AnnouncementModel($db);
    }

    public function createAnnouncement($input, $userId) {
        $courseId = $input['course_id'] ?? 0;
        $content = trim($input['content'] ?? '');

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