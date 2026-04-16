<?php
session_start();
include("config.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user'] = $user;

        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } 
    else {
        $error = "Invalid login!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    height: 100vh;
    background: linear-gradient(135deg, #ff4b2b, #6a11cb);
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

/* Background shapes */
body::before, body::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
}

body::before {
    width: 300px;
    height: 300px;
    background: #00c6ff;
    top: 10%;
    left: 10%;
}

body::after {
    width: 400px;
    height: 400px;
    background: #ff00cc;
    bottom: 10%;
    right: 10%;
}

/* Glass container */
.container {
    width: 320px;
    padding: 30px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    text-align: center;
    color: white;
}

.container h2 {
    margin-bottom: 20px;
}

/* Input */
.input-box {
    position: relative;
    margin: 15px 0;
}

.input-box input {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: none;
    outline: none;
}

/* Button */
button {
    width: 100%;
    padding: 10px;
    background: #ffc107;
    border: none;
    border-radius: 10px;
    color: black;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
}

button:hover {
    background: #ff9800;
}

/* Link */
a {
    display: block;
    margin-top: 10px;
    color: #fff;
    text-decoration: none;
    font-size: 14px;
}

/* Message */
.message {
    margin-bottom: 10px;
    color: #00ffcc;
}

.error {
    margin-bottom: 10px;
    color: #ff4b5c;
}
</style>
<body>

<div class="container">
    <h2>Login</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-box">
            <input type="text" name="username" placeholder="Username" required>
        </div>

        <div class="input-box">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <a href="register.php">Don't have an account? Sign up</a>
</div>

</body>
</html>