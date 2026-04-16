<?php
session_start();
include("config.php");

if (!isStudent()) {
    die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    $user_id = (int)$_SESSION['user']['id'];
    
    // Kiểm tra assignment có tồn tại không
    $stmt = $conn->prepare("SELECT id FROM assignments WHERE id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows == 0) {
        die("Bài tập không tồn tại");
    }
    
    // Tạo tên file an toàn
    $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $allowed_extensions = ['pdf', 'doc', 'docx', 'zip', 'rar', 'txt'];
    
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        die("Loại file không được phép");
    }
    
    $safe_filename = time() . '_' . $user_id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['file']['name']);
    $upload_path = "uploads/" . $safe_filename;
    
    // Tạo thư mục uploads nếu chưa có
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
        $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, user_id, file, submitted_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $assignment_id, $user_id, $safe_filename);
        
        if ($stmt->execute()) {
            echo "Nộp bài thành công! <a href='dashboard.php'>Quay lại</a>";
        } else {
            echo "Lỗi khi lưu vào database";
        }
    } else {
        echo "Lỗi khi upload file";
    }
} else {
    header("Location: dashboard.php");
}
?>