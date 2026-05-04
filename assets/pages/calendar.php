<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header("Location: ../index.php");
    exit;
}

$user = $_SESSION['user_data'];

$userType = $user['type'] ?? null;

$isFaculty = ($userType === 'faculty');
$isStudent = ($userType === 'student');
$isGuest   = (!$isFaculty && !$isStudent);
?>

<!DOCTYPE html>
<html>
<head>
<title>Calendar</title>
<link rel="stylesheet" href="../../assets/CSS/calendar.css">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

</head>

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
    background: white;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.card {
    background: white;
    padding: 15px;
    border-radius: 10px;
}

.logout-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
}

#calendar {
    max-width: 100%;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999; /* IMPORTANT */
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 20px;
    width: 320px;
    border-radius: 10px;
    margin: auto;
    position: relative;
}
.add-btn {
    background: #3498db;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 15px;
}
</style>

<body>

<div class="sidebar">
    <h2>Dashboard</h2>
    <div class="nav">
        <a href="../../index.php">Home</a>
        <a href="calendar.php">Course Calendar</a>
        <a href="announcements.php">Announcements</a>

        <?php if ($isFaculty): ?>
            <a href="assets/pages/student.php">Master Student List</a>
        <?php endif; ?>
    </div>
</div>

<div class="main">

    <div class="topbar">
        <h2>Calendar</h2>

        <div>
            <span><?= htmlspecialchars($user['username'] ?? 'User') ?></span>
            <form method="POST" action="../../src/Helpers/Logout.php" style="display:inline;">
                <button class="logout-btn">Logout</button>
            </form>
        </div>
    </div>

    <div class="card">
        <?php if ($isFaculty): ?>
            <button class="add-btn " onclick="openModal()">+ Add Event</button>
        <?php endif; ?>

        <div id="calendar"></div>
    </div>

</div>

<!-- Modal -->
<div class="modal" id="eventModal">
    <div class="modal-content">
        <h3>Add Event</h3>
        <input type="text" id="eventTitle" placeholder="Event Title"><br><br>
        <input type="date" id="eventDate"><br><br>
        <button onclick="addEvent()">Save</button>
        <button onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 600,

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        events: "get_events.php"
    });

    calendar.render();
});

// -------------------- MODAL --------------------

function openModal() {
    const modal = document.getElementById('eventModal');
    modal.style.display = 'flex'; // IMPORTANT instead of block
}

function closeModal() {
    const modal = document.getElementById('eventModal');
    modal.style.display = 'none';

    document.getElementById('eventTitle').value = "";
    document.getElementById('eventDate').value = "";
}

// close modal when clicking outside
document.getElementById('eventModal').addEventListener('click', function (event) {
    if (event.target === this) {
        closeModal();
    }
});

// -------------------- SAVE EVENT --------------------


</script>

</body>
</html>
