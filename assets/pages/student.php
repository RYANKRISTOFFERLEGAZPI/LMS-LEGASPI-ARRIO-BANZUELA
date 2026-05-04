<?php
session_start();

if (!isset($_SESSION['user_data'])) {
    header("Location: ../index.php");
    exit;
}

$user = $_SESSION['user_data'];
$students = $_SESSION['students'] ?? [];
$userType = $user['type'] ?? null;

$isFaculty = ($userType === 'faculty');
$isStudent = ($userType === 'student');
$isGuest   = (!$isFaculty && !$isStudent);
?>

<!DOCTYPE html>
<html>
<head>
<title>Calendar</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", sans-serif;
}

body {
    display: flex;
    background: #f1f4f9;
    color: #2c3e50;
}

.sidebar {
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, #2c3e50, #1f2a36);
    color: white;
    padding: 25px;
    box-shadow: 4px 0 12px rgba(0,0,0,0.1);
}

.sidebar h2 {
    margin-bottom: 25px;
    font-size: 22px;
}

.nav {
    display: flex;
    flex-direction: column;
    margin-top: 20px;
    gap: 8px;
}

.nav a {
    display: block;
    padding: 12px 10px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    transition: 0.2s;
    font-size: 14px;
}

.nav a:hover {
    background: rgba(255,255,255,0.12);
    transform: translateX(3px);
}

.main {
    flex: 1;
    padding: 20px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}

.topbar h2 {
    font-size: 20px;
}

.profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.username {
    font-weight: 600;
    color: #2c3e50;
}

.card {
    background: white;
    padding: 15px;
    margin-top: 20px;
    border-radius: 10px;
}

.btn, .login-btn, .logout-btn {
    padding: 10px 14px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.2s;
}
.logout-btn {
    background: #e74c3c;
    color: white;
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

        <?php if ($isFaculty): ?>
            <a href="student.php">Master Student List</a>
        <?php endif; ?>

    </div>
</div>

    <div class="main">

        <div class="topbar">
        <h2>Student List</h2>
                <div class="profile">

                    <span class="username">
                        <?= htmlspecialchars($user['username'] ?? 'User') ?>
                    </span>

                    <form method="POST" action="../../src/Helpers/Logout.php">
                        <button class="logout-btn">Logout</button>
                    </form>

                </div>

        </div>
        <?php if (empty($students)): ?>
            <p>No students found.</p>
        <?php else: ?>
            <?php foreach ($students as $student): ?>
                <div class="card">
                    <div>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($student['username']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
    </div>

</body>

</html>
