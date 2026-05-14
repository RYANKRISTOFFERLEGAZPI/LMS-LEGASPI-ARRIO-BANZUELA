<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Helpers\Database;
use App\Helpers\EnvParser;
use App\Models\StudentModel;

if (!isset($_SESSION['user_data'])) {
    header("Location: ../index.php");
    exit;
}

$env = (new EnvParser())->load(__DIR__ . '/../../.env');
$db = Database::getInstance();
$studentModel = new StudentModel($db);

$user = $_SESSION['user_data'];

$courseId = $_GET['id'] ?? null;
$courseName = $_GET['course'] ?? 'No Course';
$sectionName = $_GET['section'] ?? 'No Section';

$userType = $user['type'] ?? null;
$isFaculty = ($userType === 'faculty');
$isStudent = ($userType === 'student');

$students =$studentModel->getStudentsByCourse($courseId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Class Page</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Segoe UI", sans-serif;
}

body{
    display:flex;
    background:#f1f4f9;
    color:#2c3e50;
}

.sidebar{
    width:260px;
    height:100vh;
    background:linear-gradient(180deg, #2c3e50, #1f2a36);
    color:white;
    padding:25px;
}

.sidebar h2{
    margin-bottom:25px;
}

.nav{
    display:flex;
    flex-direction:column;
    gap:8px;
    margin-top:20px;
}

.nav a{
    text-decoration:none;
    color:white;
    padding:12px 10px;
    border-radius:8px;
    transition:.2s;
}

.nav a:hover{
    background:rgba(255,255,255,0.12);
}

.main{
    flex:1;
    padding:20px;
}

.topbar{
    background:white;
    padding:15px 20px;
    border-radius:12px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.profile{
    display:flex;
    align-items:center;
    gap:12px;
}

.username{
    font-weight:600;
}

.logout-btn{
    background:#e74c3c;
    color:white;
    border:none;
    padding:10px 14px;
    border-radius:8px;
    cursor:pointer;
}

.students-card{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

ul{
    list-style:none;
    margin-top:15px;
}

.student-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}

.actions{
    margin-top:15px;
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    align-items:center;
}

input[type="file"],
input[type="number"]{
    padding:8px;
}

.btn{
    background:#3498db;
    color:white;
    border:none;
    padding:10px 14px;
    border-radius:8px;
    cursor:pointer;
}

.btn:hover{
    background:#2980b9;
}

.grade-box{
    width:100px;
}

.file-link{
    display:inline-block;
    margin-top:10px;
    color:#3498db;
    text-decoration:none;
}

.file-link:hover{
    text-decoration:underline;
}

</style>
</head>

<body>

<div class="sidebar">

    <h2>Dashboard</h2>

    <div class="nav">

        <a href="../../index.php">Home</a>
        <a href="calendar.php">Course Calendar</a>
        <a href="announcements.php">Announcements</a>
        <a href="student.php">Students</a>

    </div>

</div>

<div class="main">

    <div class="topbar">

        <div>
                <h2>Subject: <?= htmlspecialchars($courseName) ?></h2>
                <p>Section: <?= htmlspecialchars($sectionName) ?></p>
        </div>

        <div class="profile">

            <span class="username">
                <?= htmlspecialchars($user['username'] ?? 'User') ?>
            </span>

            <form method="POST" action="../../src/Helpers/Logout.php">
                <button class="logout-btn">Logout</button>
            </form>

        </div>

    </div>

    <div class="students-card">
        <div class="student-header">
            <h3>Enrolled Students</h3>
        </div>

        <?php if (empty($students)): ?>
            <p>No students enrolled in this course.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($students as $student): ?>
                    <li><?= htmlspecialchars($student['full_name']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

</div>

</body>
</html>