<?php
session_start();
include("config.php");

if (!isAdmin()) {
    die("Access denied");
}

// ADD CLASS
if (isset($_POST['add'])) {
    $name = sanitize($_POST['name']);
    $teacher = (int)$_POST['teacher'];
    
    $stmt = $conn->prepare("INSERT INTO classes(name, teacher_id) VALUES(?, ?)");
    $stmt->bind_param("si", $name, $teacher);
    $stmt->execute();
    $stmt->close();
}

// DELETE CLASS
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// GET TEACHERS
$teachers = $conn->query("SELECT * FROM teachers ORDER BY name");

// GET CLASSES
$stmt = $conn->prepare("
    SELECT c.*, t.name as teacher 
    FROM classes c 
    LEFT JOIN teachers t ON c.teacher_id = t.id
    ORDER BY c.id DESC
");
$stmt->execute();
$classes = $stmt->get_result();
?>

<style>
    /* CSS giữ nguyên như file cũ */
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
        left: 0;
        top: 0;
    }
    .sidebar h2 {
        padding: 20px;
        text-align: center;
        background: #1ab394;
        font-size: 20px;
    }
    .sidebar a {
        display: block;
        padding: 12px 20px;
        color: #ddd;
        text-decoration: none;
        transition: 0.3s;
    }
    .sidebar a:hover {
        background: #293846;
        color: #fff;
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
        margin: 20px;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .form-box h3 {
        margin-bottom: 10px;
    }
    .form-box input,
    .form-box select {
        padding: 10px;
        margin: 5px;
        width: 220px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .form-box button {
        padding: 10px 15px;
        background: #1ab394;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .form-box button:hover {
        background: #18a689;
    }
    .table-box {
        background: white;
        margin: 20px;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .table-box h3 {
        margin-bottom: 10px;
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
    .delete-btn {
        color: red;
        font-weight: bold;
        text-decoration: none;
    }
    .delete-btn:hover {
        color: darkred;
    }
    @media (max-width: 768px) {
        .sidebar {
            width: 180px;
        }
        .main {
            margin-left: 180px;
        }
        .form-box input,
        .form-box select {
            width: 100%;
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
        Manage Classes
    </div>

    <div class="form-box">
        <h3>Add Class</h3>
        <form method="POST">
            <input name="name" placeholder="Class name" required>
            <select name="teacher" required>
                <option value="">Select Teacher</option>
                <?php while($t = $teachers->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>"><?= sanitize($t['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <button name="add">Add</button>
        </form>
    </div>

    <div class="table-box">
        <h3>Class List</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Class Name</th>
                    <th>Teacher</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = $classes->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= sanitize($c['name']) ?></td>
                    <td><?= sanitize($c['teacher'] ?? 'None') ?></td>
                    <td>
                        <a class="delete-btn" href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete?')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>