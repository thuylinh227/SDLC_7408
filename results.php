<?php
session_start();
include("config.php");

if (!isAdmin()) {
    die("Access denied");
}

// Thêm/sửa điểm
if (isset($_POST['save'])) {
    $user_id = (int)$_POST['user_id'];
    $course_id = (int)$_POST['course_id'];
    $score = (float)$_POST['score'];
    
    // Kiểm tra đã có điểm chưa
    $stmt = $conn->prepare("SELECT id FROM results WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    
    if ($exists) {
        $stmt = $conn->prepare("UPDATE results SET score = ? WHERE user_id = ? AND course_id = ?");
        $stmt->bind_param("dii", $score, $user_id, $course_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO results (user_id, course_id, score) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $user_id, $course_id, $score);
    }
    $stmt->execute();
}

// Xóa điểm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM results WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Lấy danh sách sinh viên
$students = $conn->query("SELECT id, name FROM students ORDER BY name");
// Lấy danh sách môn học
$courses = $conn->query("SELECT id, title FROM courses ORDER BY title");

// Lấy danh sách kết quả
$results = $conn->query("
    SELECT r.*, s.name as student_name, c.title as course_title
    FROM results r
    JOIN students s ON r.user_id = s.user_id
    JOIN courses c ON r.course_id = c.id
    ORDER BY r.id DESC
");
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
    body { display: flex; background: #f3f3f4; }
    .sidebar { width: 230px; height: 100vh; background: #2f4050; color: white; position: fixed; }
    .sidebar h2 { padding: 20px; background: #1ab394; text-align: center; }
    .sidebar a { display: block; padding: 12px 20px; color: #ddd; text-decoration: none; }
    .sidebar a:hover { background: #293846; color: white; }
    .main { margin-left: 230px; width: 100%; }
    .topbar { background: white; padding: 15px; font-size: 18px; font-weight: bold; }
    .form-box { background: white; padding: 20px; margin: 20px; border-radius: 5px; }
    .form-box select, .form-box input, .form-box button { padding: 10px; margin: 5px; }
    .form-box button { background: #1ab394; color: white; border: none; cursor: pointer; }
    .table-box { background: white; margin: 20px; padding: 20px; border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; }
    table th, table td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
    .delete-btn { color: red; text-decoration: none; }
</style>

<div class="sidebar">
    <h2>ADMIN</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="teachers.php">Teachers</a>
    <a href="course.php">Courses</a>
    <a href="class.php">Classes</a>
    <a href="enrollment.php">Enrollments</a>
    <a href="results.php">Results</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="topbar">Manage Results</div>
    
    <div class="form-box">
        <h3>Add/Edit Result</h3>
        <form method="POST">
            <select name="user_id" required>
                <option value="">Select Student</option>
                <?php while($s = $students->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>"><?= sanitize($s['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <select name="course_id" required>
                <option value="">Select Course</option>
                <?php while($c = $courses->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= sanitize($c['title']) ?></option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="score" step="0.1" min="0" max="10" placeholder="Score" required>
            <button name="save">Save</button>
        </form>
    </div>
    
    <div class="table-box">
        <h3>Results List</h3>
        <table>
            <thead>
                <tr><th>ID</th><th>Student</th><th>Course</th><th>Score</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php while($r = $results->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= sanitize($r['student_name']) ?></td>
                    <td><?= sanitize($r['course_title']) ?></td>
                    <td><strong><?= $r['score'] ?></strong></td>
                    <td><a class="delete-btn" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>