<?php
session_start();
include("config.php");

if (!isStudent()) {
    die("Access denied");
}

$user_id = (int) $_SESSION['user']['id'];

// Hàm kiểm tra prepare
function prepareStmt($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error . "<br>Query: " . $sql);
    }
    return $stmt;
}

// THÔNG TIN SINH VIÊN
$stmt = prepareStmt($conn, "SELECT * FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// LỚP HỌC
$stmt = prepareStmt($conn, "
    SELECT cl.name as class_name, t.name as teacher
    FROM enrollments e
    JOIN classes cl ON e.class_id = cl.id
    JOIN teachers t ON cl.teacher_id = t.id
    WHERE e.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$classes = $stmt->get_result();
$stmt->close();

// KẾT QUẢ
$stmt = prepareStmt($conn, "
    SELECT c.title as course, r.total_score as score
    FROM results r
    JOIN courses c ON r.course_id = c.id
    WHERE r.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$results = $stmt->get_result();
$stmt->close();

// BÀI TẬP
$stmt = prepareStmt($conn, "
    SELECT DISTINCT a.*
    FROM assignments a
    JOIN enrollments e ON a.course_id = e.course_id
    WHERE e.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assignments = $stmt->get_result();
$stmt->close();

// TÍNH ĐIỂM TRUNG BÌNH (dùng total_score)
$avg_score = 0;
$results_array = [];
if ($results->num_rows > 0) {
    $total = 0;
    $results->data_seek(0);
    while($r = $results->fetch_assoc()) {
        $total += $r['score'];
        $results_array[] = $r;
    }
    $avg_score = round($total / count($results_array), 1);
    $results->data_seek(0);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <meta charset="UTF-8">
    <style>
        /* CSS giữ nguyên như file cũ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            display: flex;
            background: #f3f3f4;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #2f4050;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar h2 {
            padding: 20px;
            text-align: center;
            background: #1ab394;
            font-size: 20px;
            letter-spacing: 1px;
        }
        .sidebar a {
            display: block;
            padding: 14px 20px;
            color: #ddd;
            text-decoration: none;
            transition: 0.3s;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover {
            background: #293846;
            color: white;
            border-left-color: #1ab394;
        }
        .sidebar a.active {
            background: #293846;
            border-left-color: #1ab394;
            color: white;
        }
        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }
        .topbar {
            background: white;
            padding: 18px 25px;
            font-size: 18px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar .welcome {
            color: #333;
        }
        .topbar .logout-btn {
            background: #ed5565;
            color: white;
            padding: 8px 18px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .topbar .logout-btn:hover {
            background: #da4453;
        }
        .cards {
            display: flex;
            gap: 25px;
            padding: 25px;
        }
        .card {
            flex: 1;
            padding: 25px 20px;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: default;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .card .number {
            font-size: 32px;
            font-weight: bold;
            margin-top: 10px;
        }
        .blue { background: linear-gradient(135deg, #1c84c6, #1a6fa8); }
        .green { background: linear-gradient(135deg, #1ab394, #159a7a); }
        .orange { background: linear-gradient(135deg, #f8ac59, #e0963e); }
        .purple { background: linear-gradient(135deg, #9675ce, #7c5bb5); }
        .box {
            background: white;
            margin: 0 25px 25px 25px;
            padding: 20px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .box h3 {
            margin-bottom: 18px;
            color: #2f4050;
            font-size: 18px;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 2px solid #1ab394;
            display: inline-block;
        }
        .info-grid {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .info-item {
            flex: 1;
            min-width: 200px;
        }
        .info-item label {
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-item p {
            font-size: 16px;
            color: #333;
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }
        table th {
            background: #f9f9f9;
            font-weight: 600;
            color: #555;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pass {
            background: #d4edda;
            color: #155724;
        }
        .badge-fail {
            background: #f8d7da;
            color: #721c24;
        }
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .file-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fafafa;
        }
        .btn-submit {
            background: #1ab394;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.3s;
            width: fit-content;
        }
        .btn-submit:hover {
            background: #159a7a;
        }
        .empty-row td {
            text-align: center;
            color: #999;
            padding: 30px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
            .cards {
                flex-direction: column;
            }
            .info-grid {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>📘 LMS Student</h2>
    <a href="dashboard.php" class="active">📊 Dashboard</a>
    <a href="#">📚 My Courses</a>
    <a href="#">📝 Assignments</a>
    <a href="#">📈 Results</a>
    <a href="#">👤 Profile</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <span class="welcome">👋 Xin chào, <?= sanitize($_SESSION['user']['username']) ?></span>
        <a href="logout.php" class="logout-btn">Đăng xuất</a>
    </div>

    <div class="cards">
        <div class="card blue">
            📚 Lớp học
            <div class="number"><?= $classes->num_rows ?></div>
        </div>
        <div class="card green">
            📊 Kết quả
            <div class="number"><?= count($results_array) ?></div>
        </div>
        <div class="card orange">
            📝 Bài tập
            <div class="number"><?= $assignments->num_rows ?></div>
        </div>
        <div class="card purple">
            ⭐ Điểm TB
            <div class="number"><?= $avg_score ?></div>
        </div>
    </div>

    <div class="box">
        <h3>👨‍🎓 Thông tin sinh viên</h3>
        <div class="info-grid">
            <div class="info-item">
                <label>Họ và tên</label>
                <p><?= sanitize($student['name'] ?? 'Chưa cập nhật') ?></p>
            </div>
            <div class="info-item">
                <label>Email</label>
                <p><?= sanitize($student['email'] ?? 'Chưa cập nhật') ?></p>
            </div>
            <div class="info-item">
                <label>Tài khoản</label>
                <p><?= sanitize($_SESSION['user']['username']) ?></p>
            </div>
        </div>
    </div>

    <div class="box">
        <h3>📚 Lớp học của tôi</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên lớp</th>
                    <th>Giảng viên</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 1;
                if($classes->num_rows > 0):
                    while($c = $classes->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= sanitize($c['class_name']) ?></td>
                    <td><?= sanitize($c['teacher']) ?></td>
                </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                <tr class="empty-row">
                    <td colspan="3">📭 Chưa có lớp học nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="box">
        <h3>📊 Kết quả học tập</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Môn học</th>
                    <th>Điểm số</th>
                    <th>Xếp loại</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 1;
                if(count($results_array) > 0):
                    foreach($results_array as $r):
                        $score = $r['score'];
                        $grade = '';
                        $badgeClass = '';
                        if($score >= 8.5) { $grade = 'Giỏi'; $badgeClass = 'badge-pass'; }
                        elseif($score >= 7) { $grade = 'Khá'; $badgeClass = 'badge-pass'; }
                        elseif($score >= 5) { $grade = 'Trung bình'; $badgeClass = 'badge-pass'; }
                        else { $grade = 'Yếu'; $badgeClass = 'badge-fail'; }
                ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= sanitize($r['course']) ?></td>
                    <td><strong><?= $score ?></strong></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $grade ?></span></td>
                </tr>
                <?php 
                    endforeach;
                else: 
                ?>
                <tr class="empty-row">
                    <td colspan="4">📭 Chưa có kết quả nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="box">
        <h3>📝 Bài tập cần nộp</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tiêu đề bài tập</th>
                    <th>Mô tả</th>
                    <th>Hạn nộp</th>
                    <th>Nộp bài</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 1;
                if($assignments->num_rows > 0):
                    while($a = $assignments->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><strong><?= sanitize($a['title']) ?></strong></td>
                    <td><?= sanitize(substr($a['description'] ?? '', 0, 50)) ?>...</td>
                    <td><span style="color: #e0963e;">📅 <?= sanitize($a['due_date']) ?></span></td>
                    <td>
                        <form action="submit_assignment.php" method="POST" enctype="multipart/form-data" class="upload-form">
                            <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                            <input type="file" name="file" required class="file-input">
                            <button type="submit" class="btn-submit">📤 Nộp bài</button>
                        </form>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                <tr class="empty-row">
                    <td colspan="5">📭 Hiện không có bài tập nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>