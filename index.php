<?php
session_start();
require_once "./vendor/autoload.php";

use App\Helpers\Database;
use App\Helpers\EnvParser;
use App\Controllers\UserController;
use App\Controllers\CourseController;
use App\Models\UserModel;
use App\Models\CourseModel;
use App\Controllers\AnnouncementController;
use App\Models\AnnouncementModel;


$env = (new EnvParser())->load(__DIR__ . '/.env');
$db = Database::getInstance();
$controller = new UserController($db);
$courseController = new CourseController($db);
$announcementModel = new AnnouncementModel($db);
$announcementController = new AnnouncementController($db);
$courseModel = new CourseModel($db);
$userModel = new UserModel($db);


# ========================= ROLE SYSTEM =========================
$user = $_SESSION['user_data'] ?? null;
$userType = $user['type'] ?? null;
$_SESSION['students'] = $controller->getStudents();
$_SESSION['announcement'] = $announcementModel->getAnnouncements();


$isFaculty = ($userType === 'faculty');
$isStudent = ($userType === 'student');
$isGuest   = (!$isFaculty && !$isStudent);

# ========================= COURSE SYSTEM =========================
$courses = $courseController->getCourses();
if ($isStudent) {
    $joinedCourses = $courseController->getJoinedCourses($user['id']);
}

# ========================= HANDLER =========================
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $action = $_POST['action'] ?? '';

    if ($action === 'register') {

        $data = [
            "username" => $_POST["username"] ?? '',
            "password" => $_POST["password"] ?? '',
            "first_name" => $_POST["first_name"] ?? '',
            "last_name" => $_POST["last_name"] ?? '',
            "email" => $_POST["email"] ?? '',
            "confirm_password" => $_POST["confirm_password"] ?? '',
            "type" => $_POST["type"] ?? 'guest'
        ];

        $result = $controller->register($data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = implode("<br>", $result['errors']);
        }
    }

    if ($action === 'login') {

        $data = [
            "username" => $_POST["username"] ?? '',
            "password" => $_POST["password"] ?? '',
        ];

        $result = $controller->loginValidate($data);

        if ($result['success']) {
            $_SESSION['user_data'] = $result['data'];
            $_SESSION['user_type'] = $result['data']['type'] ?? 'guest';
            $_SESSION['success'] = "Login successful! Welcome " . $result['data']['username'];
        } else {
            $_SESSION['error'] = implode("<br>", $result['errors']);
        }
    }

    if ($action === 'create_class') {

        $data = [
            "name" => $_POST["name"] ?? '',
            "section" => $_POST["section"] ?? '',
        ];

        $result = $courseController->createClass($data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = implode("<br>", $result['errors']);
        }
    }

    if ($action === 'join_class') {

        if ($isStudent) {
            $code = $_POST['code'] ?? '';
            $userID = $user['id'] ?? 0;

            $result = $courseController->joinClass($code,$userID);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = implode("<br>", $result['errors']);
            }
        } else {
            $_SESSION['error'] = 'Only students can join classes.';
        }
    }

    if ($action === 'delete_course') {

        $id = $_POST['id'] ?? 0;
        $result = $courseController->deleteCourse($id);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = implode("<br>", $result['errors']);
        }
    }

    if ($action === 'create_announcement') {

        if ($isFaculty) {
            $data = [
                "content" => $_POST["content"] ?? '',
                "course_id" => $_POST["course_id"] ?? 0,
                "course_name" => $courseModel->getById($_POST["course_id"] ?? 0)['name'] ?? 'Unknown Course'
            ];

            $result = $announcementController->createAnnouncement($data, $user['id']);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = implode("<br>", $result['errors']);
            }
        } else {
            $_SESSION['error'] = 'Only faculty can post announcements.';
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/index.css">
<title>Classroom Dashboard</title>

</head>

<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">
    <h2>Dashboard</h2>

    <div class="nav">

        <a href="index.php">Home</a>

        <?php if(!$isGuest): ?>
            <a href="assets/pages/calendar.php">Course Calendar</a>
            <a href="assets/pages/announcements.php">Announcements</a>
        <?php endif; ?>

        <?php if ($isFaculty): ?>
            <a href="assets/pages/student.php">Master Student List</a>
        <?php endif; ?>

    </div>
</div>

<!-- ================= MAIN ================= -->
<div class="main">

    <div class="topbar">
        <h2>Learning Management System</h2>

        <?php if ($isGuest): ?>
            <div>
                <span>Guest</span>
                <button class="login-btn" onclick="document.getElementById('register-modal').style.display='block'">
                    Register
                </button>
                <button class="login-btn" onclick="document.getElementById('login-modal').style.display='block'">
                    Login
                </button>
            </div>
        <?php else: ?>
            <div class="profile">

                <span class="username">
                    <?= htmlspecialchars($user['username'] ?? 'User') ?>
                </span>

                <form method="POST" action="src/Helpers/Logout.php">
                    <button class="logout-btn">Logout</button>
                </form>

            </div>
        <?php endif; ?>

    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="feedback success">
            <span class="close-btn" onclick="this.parentElement.style.display='none'">&times;</span>
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="feedback error">
            <span class="close-btn" onclick="this.parentElement.style.display='none'">&times;</span>
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- ===== WELCOME SECTION ===== -->
    <div class="welcome-banner">
        <h1>Welcome to <span class="highlight">University Academy</span> 🎓</h1>
        <p>
            Your digital learning space where students and faculty connect, 
            collaborate, and grow together.
        </p>

        <?php if(!$isGuest): ?>
            <p style="margin-top:10px;">
                Hello, <strong><?= htmlspecialchars($user['username']) ?></strong> 👋
            </p>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="feedback success">
            <span class="close-btn" onclick="this.parentElement.style.display='none'">X</span>
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="feedback error">
            <span class="close-btn" onclick="this.parentElement.style.display='none'">X</span>
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- ================= FACULTY VIEW ================= -->
    <?php if ($isFaculty): ?>

        <button class="btn" onclick="document.getElementById('modal').style.display='block'">
            Create Class
        </button>

        <button class="btn" onclick="document.getElementById('announcement').style.display='block'">
            Create Announcement
        </button>

<!-- ======class list====== -->
    <div class="classes">
    <?php if (empty($courses)): ?>
        <p>No courses available.</p>
    <?php else: ?>
        <?php foreach ($courses as $course): ?>
            
            <div class="card" 
                <div class="card"
                onclick="window.location.href='assets/pages/class.php?id=<?php echo $course['id']; ?>&course=<?php echo urlencode($course['name']); ?>&section=<?php echo urlencode($course['section']); ?>'">
                
                <p><strong>Name:</strong> <?php echo htmlspecialchars($course['name']); ?></p>
                <p><strong>Section:</strong> <?php echo htmlspecialchars($course['section']); ?></p>
                <p><strong>Code:</strong> <?php echo htmlspecialchars($course['code']); ?></p>

                <div class="menu-container">
                    <button class="menu-btn" 
                            onclick="event.stopPropagation(); toggleMenu(this)">
                        ⋮
                    </button>

                    <div class="menu-dropdown">

                        <form method="POST" style="margin:0;"
                              onclick="event.stopPropagation();">
                            <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                            <button type="submit" name="action" class="menu-btn" value="delete_course"
                                onclick="return confirm('Delete this course?')">
                                Delete
                            </button>
                        </form>

                        <form method="POST" style="margin:0;"
                              onclick="event.stopPropagation();">
                            <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                            <button type="submit" name="action" value="hide_course">
                                Hide
                            </button>
                        </form>

                        <a href="people.php?id=<?php echo $course['id']; ?>"
                           onclick="event.stopPropagation();">
                           People
                        </a>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>
</div>

    <?php endif; ?>
    <!-- ================= STUDENT VIEW ================= -->
    <?php if ($isStudent): ?>

        <form method="POST" class="join-class">
            <input type="hidden" name="action" value="join_class">
            <h3>Join Class</h3>
            <input type="text" name="code" placeholder="Enter Class Code" required>
            <button class="btn">Join Class</button>
        </form>

        <?php if (empty($joinedCourses)): ?>
            <p>No courses available.</p>
        <?php else: ?>
            <?php foreach ($joinedCourses as $course): ?>

                <?php
                    $url = "assets/pages/class.php?id=" . urlencode($course['id']) .
                        "&course=" . urlencode($course['name']) .
                        "&section=" . urlencode($course['section']);
                ?>

                <div class="card"
                    onclick="window.location.href='<?= $url ?>'">

                    <div>
                        <p><strong>Name:</strong> <?= htmlspecialchars($course['name']) ?></p>
                        <p><strong>Section:</strong> <?= htmlspecialchars($course['section']) ?></p>
                    </div>

                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ================= CREATE CLASS MODAL ================= -->
<div id="modal" class="modal">
    <div class="modal-content">
        <form method="POST">
            <input type="hidden" name="action" value="create_class">
            <h3>Create Class</h3>
            <input type="text" name="name" placeholder="Subject Name" required>
            <input type="text" name="section" placeholder="Section" required>
            <button class="btn">Create</button>
            <button class="btn" onclick="document.getElementById('modal').style.display='none'">Close</button>
        </form>
    </div>
</div>

<!-- ================= LOGIN MODAL ================= -->
<div id="login-modal" class="modal">
    <div class="modal-content">
        <h3>USER LOGIN</h3>

        <form method="POST">
            <input type="hidden" name="action" value="login">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button class="btn">Login</button>
            <button class="btn" onclick="document.getElementById('login-modal').style.display='none'">
                Close
            </button>
        </form>
    </div>
</div>

<!-- ================= ANNOUNCEMENT MODAL ================= -->
<div id="announcement" class="modal">
    <div class="modal-content">
        <form method="POST">
            <input type="hidden" name="action" value="create_announcement">

            <select name="course_id" required>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>">
                        <?php echo htmlspecialchars($course['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <textarea class="text-area" name="content" placeholder="Write announcement..." required></textarea>

            <button class="btn">Post Announcement</button>
            <button class="btn" onclick="document.getElementById('announcement').style.display='none'">
                Close
            </button>
        </form>
    </div>
</div>
<!-- ================= REGISTER MODAL ================= -->
<div id="register-modal" class="modal">
    <div class="modal-content">

        <form id="registrationForm" method="POST">
            <input type="hidden" name="action" value="register">

            <h2>Register</h2>
            <input type="text" 
                    name="username" 
                    id="username" 
                    placeholder="Enter your username (min. 3 characters)"
                    required
                    pattern="[a-zA-Z][a-zA-Z0-9_.]*"
                    title="Username must start with a letter and can only contain letters, numbers, underscores and dots">
            <div id="usernameStatus" class="username-status"></div>

            <input type="text"
                    name="first_name"
                    id="first_name"
                    placeholder="First Name"
                    required>

            <input type="text" 
                    name="last_name"
                    id="last_name"
                    placeholder="Last Name"
                    required>

            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            
            <div class="role-group">
                <label class="role-option">
                    <input type="radio" name="type" value="student" checked>
                    <span>Student</span>
                </label>

                <label class="role-option">
                    <input type="radio" name="type" value="faculty">
                    <span>Faculty</span>
                </label>
            </div>
            <button class="btn">Register</button>
            <button class="btn" onclick="document.getElementById('register-modal').style.display='none'">
                Close
            </button>
        </form>
    </div>
</div>
</body>

<script>

document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            const usernameStatus = document.getElementById('usernameStatus');
            const emailInput = document.getElementById('email');
            const emailStatus = document.getElementById('emailStatus');
            const submitBtn = document.getElementById('submitBtn');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_pass');
            let userTimeoutId;
            let emailTimeoutId;

            // Username availability check
            usernameInput.addEventListener('input', function() {
                const username = this.value;
                
                // Clear previous timeout
                if (userTimeoutId) {
                    clearTimeout(userTimeoutId);
                }

                // Client-side validation
                if (username.length === 0) {
                    usernameStatus.textContent = 'Username is required';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (username.length < 3) {
                    usernameStatus.textContent = 'Username must be at least 3 characters';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (!/^[a-zA-Z]/.test(username)) {
                    usernameStatus.textContent = 'Username must start with a letter';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (!/^[a-zA-Z][a-zA-Z0-9_.]*$/.test(username)) {
                    usernameStatus.textContent = 'Username can only contain letters, numbers, underscores and dots';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                // Show checking status
                usernameStatus.textContent = 'Checking availability...';
                usernameStatus.className = 'username-status checking';
                submitBtn.disabled = true;

                // Set timeout to avoid too many requests
                userTimeoutId = setTimeout(() => {
                    checkUsernameAvailability(username);
                }, 500);
            });

            // Username availability check
            function checkUsernameAvailability(username) {
                fetch('src/APIs/UserAPI.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username: username })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        usernameStatus.textContent = data.errors.join(', ');
                        usernameStatus.className = 'username-status unavailable';
                        submitBtn.disabled = true;
                    } else if (data.available) {
                        usernameStatus.textContent = '✓ Username is available!';
                        usernameStatus.className = 'username-status available';
                        submitBtn.disabled = false;
                    } else {
                        usernameStatus.textContent = '✗ Username is already taken';
                        usernameStatus.className = 'username-status unavailable';
                        submitBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    usernameStatus.textContent = 'Error checking username. Please try again.';
                    usernameStatus.className = 'username-status unavailable';
                });
            }

            // Password match validation
            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirm = confirmInput.value;
                
                if (confirm.length > 0) {
                    if (password !== confirm) {
                        confirmInput.setCustomValidity('Passwords do not match');
                    } else {
                        confirmInput.setCustomValidity('');
                    }
                }
            }

            passwordInput.addEventListener('change', checkPasswordMatch);
            confirmInput.addEventListener('keyup', checkPasswordMatch);

            // Form submission validation
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                if (submitBtn.disabled) {
                    e.preventDefault();
                    alert('Please fix the username issues before submitting.');
                }
                
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                }
            });
        });

    function toggleMenu(button) {
        let menu = button.nextElementSibling;

        document.querySelectorAll('.menu-dropdown').forEach(m => {
            if (m !== menu) m.style.display = 'none';
        });

        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.menu-container')) {
            document.querySelectorAll('.menu-dropdown').forEach(m => {
                m.style.display = 'none';
            });
        }
    });
</script>

</html>