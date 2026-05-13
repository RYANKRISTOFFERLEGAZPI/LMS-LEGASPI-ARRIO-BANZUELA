<?php

namespace App\Models;

class StudentModel {

    private $conn;

    public function __construct($database){
        $this->conn = $database;
    }

   

    public function getStudentsByCourse($courseId){
        $sql = "
            SELECT 
                users.id,
                users.full_name,
                users.username
            FROM enrollments

            INNER JOIN users
                ON users.id = enrollments.user_id

            WHERE enrollments.course_id = '$courseId'
            AND users.type = 'student'
        ";

        $result = $this->conn->query($sql);

        $students = [];

        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        return $students;
    }

}
?>