<?php
session_start();
include("config.php");

if (!isAdmin()) {
    die("Access denied");
}

// ADD
if (isset($_POST['add'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    
    $stmt = $conn->prepare("INSERT INTO teachers(name, email) VALUES(?, ?)");
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    $stmt->close();
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// GET DATA
$stmt = $conn->prepare("SELECT * FROM teachers ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
    /* CSS giống như students.php nhưng đổi màu */
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
    .form-box h3 {
        margin-bottom: 10px;
    }
    .form-box input {
        padding: 10px;
        margin: 5px;
        width: 220px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }
    .form-box button {
        padding: 10px 15px;
        background: #1ab394;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .form-box button:hover {
        background: #18a689;
    }
    .table-box {
        background: white;
        margin: 20px;
        padding: 20px;
        border-radius: 5px;
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
        text-decoration: none;
        font-weight: bold;
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
        Manage Teachers
    </div>

    <div class="form-box">
        <h3>Add Teacher</h3>
        <form method="POST">
            <input name="name" placeholder="Name" required>
            <input name="email" placeholder="Email" required>
            <button name="add">Add</button>
        </form>
    </div>

    <div class="table-box">
        <h3>Teacher List</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($t = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= sanitize($t['name']) ?></td>
                    <td><?= sanitize($t['email']) ?></td>
                    <td>
                        <a class="delete-btn" href="?delete=<?= $t['id'] ?>" onclick="return confirm('Delete?')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>