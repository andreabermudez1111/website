<?php
require_once 'config.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (!empty($username) && !empty($email) && !empty($password)) {
        if ($password !== $confirm_password) {
            $error = "Confirm password did not match.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "The username or email is already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("sss", $username, $email, $hashed_password);
                
                if ($stmt_insert->execute()) {
                    $success = "Registration completed successfully! Redirecting you to login...";
                    header("refresh:2; url=login.php");
                } else {
                    $error = "Failed to create user.";
                }
                $stmt_insert->close();
            }
            $stmt->close();
        }
    } else {
        $error = "Please fill out all the fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Profile | G's Coffee Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: #f9f6f0; display: flex; justify-content: center; align-items: center; min-height: 100vh;">

    <div class="auth-box">
        <h2>Create Account</h2>
        <p>Register to customize, submit and monitor your tray selections.</p>
        
        <?php if (!empty($error)): ?>
            <div class="error-alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-alert"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div class="auth-field-node">
                <label>Choose Username</label>
                <input type="text" name="username" placeholder="Write custom handle" required autocomplete="off">
            </div>

            <div class="auth-field-node">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="example@gscoffee.com" required autocomplete="off">
            </div>
            
            <div class="auth-field-node">
                <label>Secure Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="auth-field-node">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="cta-order-btn" style="width: 100%; margin-top: 15px;">Register Account Profile</button>
        </form>
        
        <p class="auth-redirect-link">Already verified? <a href="login.php">Log In here</a></p>
        <p style="text-align: center; margin-top: 15px;"><a href="index.php" style="color:#666; font-size: 13px;">← Return to Main Page</a></p>
    </div>

</body>
</html>