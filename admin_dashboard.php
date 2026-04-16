<?php
session_start();
include("config.php");

if (!isAdmin()) {
    die("Access denied");
}

// Lấy số liệu thật từ DB
$students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc();
$teachers = $conn->query("SELECT COUNT(*) as total FROM teachers")->fetch_assoc();
$courses = $conn->query("SELECT COUNT(*) as total FROM courses")->fetch_assoc();
$classes = $conn->query("SELECT COUNT(*) as total FROM classes")->fetch_assoc();
?>

<style>
    /* CSS giữ nguyên như cũ */
    body {
        margin: 0;
        font-family: Arial;
        display: flex;
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
        margin: 0;
        background: #1ab394;
    }
    .sidebar a {
        display: block;
        padding: 12px 20px;
        color: #ddd;
        text-decoration: none;
    }
    .sidebar a:hover {
        background: #293846;
    }
    .main {
        margin-left: 230px;
        width: 100%;
        background: #f3f3f4;
        min-height: 100vh;
    }
    .topbar {
        background: white;
        padding: 15px;
    }
    .cards {
        display: flex;
        gap: 20px;
        padding: 20px;
    }
    .card {
        flex: 1;
        padding: 20px;
        color: white;
        border-radius: 5px;
    }
    .red { background: #ed5565; }
    .blue { background: #1c84c6; }
    .green { background: #1ab394; }
    .orange { background: #f8ac59; }
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
    <div class="topbar">
        Welcome <?= sanitize($_SESSION['user']['username']) ?>
    </div>
    <div class="cards">
        <div class="card red">
            Students<br><h2><?= $students['total'] ?></h2>
        </div>
        <div class="card blue">
            Teachers<br><h2><?= $teachers['total'] ?></h2>
        </div>
        <div class="card green">
            Courses<br><h2><?= $courses['total'] ?></h2>
        </div>
        <div class="card orange">
            Classes<br><h2><?= $classes['total'] ?></h2>
        </div>
    </div>
</div>