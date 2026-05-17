<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['user_data'])) {
    header("Location: ../index.php");
    exit;
}

$user = $_SESSION['user_data'];
$announcements = $_SESSION['announcement'] ?? [];
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
    overflow-y: auto;
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
        <a href="announcements.php">Announcements</a>

    </div>
</div>

    <div class="main">

        <div class="topbar">
        <h2>Announcements</h2>

            <?php if ($isGuest): ?>
                <div>
                    <span>Guest</span>
                    <button class="login-btn" onclick="document.getElementById('login-modal').style.display='block'">
                        Login
                    </button>
                </div>
            <?php else: ?>
                <div class="profile">

                    <span class="username">
                        <?= htmlspecialchars($user['username'] ?? 'User') ?>
                    </span>

                    <form method="POST" action="../../src/Helpers/Logout.php">
                        <button class="logout-btn">Logout</button>
                    </form>

                </div>
            <?php endif; ?>

        </div>

        <div class="card">
            <?php if(empty($announcements)): ;?>
                <p>No announcements available.</p>
            <?php else: ;?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($announcement['course_name']); ?></h3>
                    <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                    <small><?php echo $announcement['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</body>

</html>