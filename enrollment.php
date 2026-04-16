<?php
session_start();
include("config.php");

if (!isAdmin()) {
    die("Access denied");
}

// Thêm ghi danh
if (isset($_POST['add'])) {
    $user_id = (int)$_POST['user_id'];
    $class_id = (int)$_POST['class_id'];
    $course_id = (int)$_POST['course_id'];
    
    $stmt = $conn->prepare("INSERT INTO enrollments (user_id, class_id, course_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $class_id, $course_id);
    $stmt->execute();
}

// Xóa ghi danh
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Lấy danh sách sinh viên
$students = $conn->query("SELECT id, name FROM students ORDER BY name");
// Lấy danh sách lớp
$classes = $conn->query("SELECT id, name FROM classes ORDER BY name");
// Lấy danh sách môn học
$courses = $conn->query("SELECT id, title FROM courses ORDER BY title");

// Lấy danh sách ghi danh
$enrollments = $conn->query("
    SELECT e.*, s.name as student_name, c.name as class_name, co.title as course_title
    FROM enrollments e
    JOIN students s ON e.user_id = s.user_id
    JOIN classes c ON e.class_id = c.id
    JOIN courses co ON e.course_id = co.id
    ORDER BY e.id DESC
");
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }
    body {
        display: flex;
        background: #f3f3f4;
    }
    .sidebar {
        width: 230px;
        height: 100vh;
        background: #2f4050;
        color: white;
        position: fixed;
    }
    .sidebar h2 {
        padding: 20px;
        background: #1ab394;
        text-align: center;
    }
    .sidebar a {
        display: block;
        padding: 12px 20px;
        color: #ddd;
        text-decoration: none;
    }
    .sidebar a:hover {
        background: #293846;
        color: white;
    }
    .main {
        margin-left: 230px;
        width: 100%;
    }
    .topbar {
        background: white;
        padding: 15px;
        font-size: 18px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .form-box {
        background: white;
        padding: 20px;
        margin: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .form-box select, .form-box button {
        padding: 10px;
        margin: 5px;
    }
    .form-box button {
        background: #1ab394;
        color: white;
        border: none;
        cursor: pointer;
    }
    .table-box {
        background: white;
        margin: 20px;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table th, table td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    .delete-btn {
        color: red;
        text-decoration: none;
    }
</style>

<div class="sidebar">
    <h2>ADMIN</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="teachers.php">Teachers</a>
    <a href="course.php">Courses</a>
    <a href="class.php">Classes</a>
    <a href="enrollment.php">Enrollments</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="topbar">Manage Enrollments</div>
    
    <div class="form-box">
        <h3>Add Enrollment</h3>
        <form method="POST">
            <select name="user_id" required>
                <option value="">Select Student</option>
                <?php while($s = $students->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>"><?= sanitize($s['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php while($c = $classes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <select name="course_id" required>
                <option value="">Select Course</option>
                <?php while($co = $courses->fetch_assoc()): ?>
                    <option value="<?= $co['id'] ?>"><?= sanitize($co['title']) ?></option>
                <?php endwhile; ?>
            </select>
            <button name="add">Add</button>
        </form>
    </div>
    
    <div class="table-box">
        <h3>Enrollment List</h3>
        <table>
            <thead>
                <tr><th>ID</th><th>Student</th><th>Class</th><th>Course</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php while($e = $enrollments->fetch_assoc()): ?>
                <tr>
                    <td><?= $e['id'] ?></td>
                    <td><?= sanitize($e['student_name']) ?></td>
                    <td><?= sanitize($e['class_name']) ?></td>
                    <td><?= sanitize($e['course_title']) ?></td>
                    <td><a class="delete-btn" href="?delete=<?= $e['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>