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
$conn = $db->getConnection();
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
    if (isset($_FILES['attachment'])) {

        $file = $_FILES['attachment'];

        if ($file['error'] === 0) {

            $uploadDir = __DIR__ . '/../../uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;

            move_uploaded_file($file['tmp_name'], $filePath);

            $stmt = $conn->prepare("INSERT INTO attachments (course_id, file_name, file_path, uploaded_by)
                                VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $courseId,
                $file['name'],
                $fileName,
                $user['id']
            ]);

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
    // ================= ACTIVITY CREATE =================
    if (isset($_POST['activity_title']) && $isFaculty) {

        $title = $_POST['activity_title'];
        $description = $_POST['activity_description'];

        $fileName = null;

        if (!empty($_FILES['activity_file']['name'])) {

            $file = $_FILES['activity_file'];
            $uploadDir = __DIR__ . '/../../uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);
        }

        $stmt = $conn->prepare("
            INSERT INTO activities (course_id, title, description, file_name, file_path, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $courseId,
            $title,
            $description,
            $file['name'] ?? null,
            $fileName,
            $user['id']
        ]);

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    // ================= STUDENT SUBMISSION =================
    if (isset($_POST['activity_id']) && $isStudent) {

        $activityId = $_POST['activity_id'];
        $file = $_FILES['submission'];

        if ($file['error'] === 0) {

            $uploadDir = __DIR__ . '/../../uploads/';

            $fileName = time() . '_' . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);

            // prevent duplicate submission (optional)
            $check = $conn->prepare("
                SELECT * FROM submissions WHERE activity_id = ? AND student_id = ?
            ");
            $check->execute([$activityId, $user['id']]);

            if ($check->rowCount() == 0) {

                $stmt = $conn->prepare("
                    INSERT INTO submissions (activity_id, student_id, file_name, file_path)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->execute([
                    $activityId,
                    $user['id'],
                    $file['name'],
                    $fileName
                ]);
            }

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}


$stmt = $conn->prepare("SELECT * FROM attachments WHERE course_id = ?");
$stmt->execute([$courseId]);
$attachments = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT * FROM activities WHERE course_id = ?");
$stmt->execute([$courseId]);
$activities = $stmt->fetchAll();
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
    background:linear-gradient(180deg, #2c3e50, #1f2a36);
    color:white;
    padding:25px;
    overflow-y: auto;
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

.students-card, .attachment-card{
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

<!-- PDF.js for PDF files -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    
    <!-- docx-preview for DOCX files -->
     <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/docx-preview-lib@0.1.14-fix-3/dist/docx-preview.min.js"></script>

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

    <div class="attachment-card">
        <div class="attachment-header">
            <h3>Attachments</h3>
        </div>    
        
        <?php if ($isFaculty): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="attachment" required>
                <button type="submit" class="btn">Upload</button>
            </form>
        <?php endif; ?>

        <?php if (empty($attachments)): ?>
            <p>No attachments available.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($attachments as $file): ?>
                    <li>
                        <a class="file-link" 
                        href="../../uploads/<?= htmlspecialchars($file['file_path']) ?>" 
                        target="_blank">
                        <?= htmlspecialchars($file['file_name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="attachment-card">
        <h3>Activities</h3>

        <?php if ($isFaculty): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="activity_title" placeholder="Activity Title" required>
                <input type="text" name="activity_description" placeholder="Description">
                <input type="file" name="activity_file">
                <button class="btn">Create Activity</button>
            </form>
        <?php endif; ?>

        <?php if (empty($activities)): ?>
            <p>No activities available.</p>
        <?php else: ?>

            <?php foreach ($activities as $activity): ?>

                <div style="margin-top:20px; padding:15px; border:1px solid #ddd; border-radius:10px;">

                    <h4><?= htmlspecialchars($activity['title']) ?></h4>
                    <p><?= htmlspecialchars($activity['description']) ?></p>

                    <?php if ($activity['file_path']): ?>
                        <a class="file-link" target="_blank"
                        href="../../uploads/<?= $activity['file_path'] ?>">
                        View Activity File
                        </a>
                    <?php endif; ?>

                    <!-- ================= STUDENT SUBMIT ================= -->
                    <?php if ($isStudent): ?>

                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="activity_id" value="<?= $activity['id'] ?>">
                            <input type="file" name="submission" required>
                            <button class="btn">Submit Work</button>
                        </form>

                    <?php endif; ?>

                    <!-- ================= SHOW SUBMISSIONS ================= -->
                    <?php
                        $stmt = $conn->prepare("
                            SELECT s.*, u.username 
                            FROM submissions s
                            JOIN users u ON u.id = s.student_id
                            WHERE s.activity_id = ?
                        ");
                        $stmt->execute([$activity['id']]);
                        $subs = $stmt->fetchAll();
                    ?>

                    <?php if ($isFaculty): ?>
                        <h5>Submissions:</h5>

                        <?php foreach ($subs as $sub): ?>
                            <div style="margin-bottom:10px;">
                                <strong><?= htmlspecialchars($sub['username']) ?></strong>

                                <a href="../../uploads/<?= $sub['file_path'] ?>" target="_blank">
                                    View
                                </a>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                    <input type="number" name="grade" placeholder="Grade"
                                        value="<?= $sub['grade'] ?>" class="grade-box">
                                    <button class="btn">Save</button>
                                </form>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                    <!-- ================= STUDENT VIEW GRADE ================= -->
                    <?php if ($isStudent): 
                        foreach ($subs as $sub):
                            if ($sub['student_id'] == $user['id']):
                    ?>
                        <p><strong>Your Grade:</strong> <?= $sub['grade'] ?? 'Not graded yet' ?></p>
                    <?php 
                            endif;
                        endforeach;
                    endif; ?>

                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>


<script>
    const fileType = '<?php echo $ext; ?>';
    const filePath = '<?php echo $filePath; ?>';
    
    if (fileType === 'pdf') {
        // Show PDF viewer
        document.getElementById('pdf-viewer').style.display = 'block';
        
        // Load and render PDF
        pdfjsLib.getDocument(filePath).promise.then(function(pdf) {
            pdf.getPage(1).then(function(page) {
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                const viewport = page.getViewport({ scale: 1.5 });
                
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                document.getElementById('pdf-viewer').appendChild(canvas);
                
                page.render({
                    canvasContext: context,
                    viewport: viewport
                });
            });
        });
        
    } else if (fileType === 'docx') {
        // Show DOCX viewer
        const container = document.getElementById('docx-viewer');
        container.style.display = 'block';
        
        // Fetch and render DOCX
        fetch(filePath)
        .then(response => response.arrayBuffer())
        .then(buffer => {
        docx.renderAsync(buffer, container, null, {
        className: "docx", // CSS class for the wrapper
        inWrapper: true,   // Enable wrapper around document
        ignoreWidth: false,
        ignoreHeight: false,
        breakPages: true,
        debug: false
        }).then(function() {
            console.log("Document rendered successfully");
        });
        })
        .catch(error => {
            container.innerHTML = '<p style="color: red;">Error loading DOCX file: ' + error.message + '</p>';
        });
    }
</script>
</body>
</html>