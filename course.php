<?php
session_start();
include("config.php");

if (!isAdmin()) {
    die("Access denied");
}

// ADD COURSE
if ($_POST) {
    $title = sanitize($_POST['title']);
    $teacher = (int)$_POST['teacher'];
    
    $stmt = $conn->prepare("INSERT INTO courses(title, teacher_id) VALUES(?, ?)");
    $stmt->bind_param("si", $title, $teacher);
    $stmt->execute();
    $stmt->close();
}

// GET COURSES
$stmt = $conn->prepare("
    SELECT c.*, t.name as teacher 
    FROM courses c 
    LEFT JOIN teachers t ON c.teacher_id = t.id
    ORDER BY c.id DESC
");
$stmt->execute();
$courses = $stmt->get_result();

// GET TEACHERS FOR DROPDOWN
$teachers_result = $conn->query("SELECT * FROM teachers ORDER BY name");
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
    .form-box input, .form-box select {
        padding: 10px;
        margin: 5px;
        width: 220px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }
    .form-box button {
        padding: 10px 15px;
        background: #1c84c6;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .form-box button:hover {
        background: #1ab394;
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
    table th {
        background: #f5f5f5;
    }
    table tr:hover {
        background: #f1f1f1;
    }
    @media (max-width: 768px) {
        .sidebar {
            width: 180px;
        }
        .main {
            margin-left: 180px;
        }
    }
</style>

<div class="sidebar">
    <h2>ADMIN</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="teachers.php">Teachers</a>
    <a href="course.php">Courses</a>
    <a href="class.php">Classes</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="topbar">
        Manage Courses
    </div>

    <div class="form-box">
        <h3>Add Course</h3>
        <form method="POST">
            <input name="title" placeholder="Course title" required>
            <select name="teacher" required>
                <option value="">Select Teacher</option>
                <?php while($t = $teachers_result->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>"><?= sanitize($t['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Add</button>
        </form>
    </div>

    <div class="table-box">
        <h3>Course List</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course</th>
                    <th>Teacher</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = $courses->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= sanitize($c['title']) ?></td>
                    <td><?= sanitize($c['teacher'] ?? 'Not assigned') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>