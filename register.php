<?php
include("config.php");

$message = "";

if ($_POST) {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "Passwords do not match!";
    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users(username,password,role) VALUES(?,?,?)");

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $role = "student";

        $stmt->bind_param("sss", $username, $hash, $role);

        if ($stmt->execute()) {
            $message = "Register success!";
        } else {
            $message = "Error!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
    <h2>Register</h2>

    <?php if ($message): ?>
        <div class="<?php echo (strpos($message,'success')!==false)?'message':'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-box">
            <input type="text" name="username" placeholder="Username" required>
        </div>

        <div class="input-box">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="input-box">
            <input type="password" name="confirm" placeholder="Confirm Password" required>
        </div>

        <button type="submit">Register</button>
    </form>

    <a href="login.php">Already have account? Login</a>
</div>

</body>
</html>