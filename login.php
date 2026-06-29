<?php
require_once 'config.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid account credentials password.";
            }
        } else {
            $error = "User profile account does not exist.";
        }
        $stmt->close();
    } else {
        $error = "All authorization fields are required.";
    }
}
?>

<?php include 'header.php'; ?>

<!-- Ginamit natin ang margin para pumagitna nang maayos sa pagitan ng Header at Footer -->
<div class="auth-box" style="margin: 100px auto;">
    <h2>Sign In</h2>
    <p>Access your user account profiles safely.</p>
    
    <?php if (!empty($error)): ?>
        <div class="error-alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
        <div class="auth-field-node">
            <label>Username or Email Address</label>
            <input type="text" name="username" placeholder="Registered handle..." required autocomplete="off">
        </div>
        
        <div class="auth-field-node">
            <label>Account Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        
        <!-- Pinalitan ng checkout-submit-btn para pumasok sa theme -->
        <button type="submit" class="checkout-submit-btn">Authenticate Account</button>
    </form>
    
    <div class="auth-redirect-link">
        <p style="margin-bottom: 10px;">First time client? <a href="register.php">Create local profile account</a></p>
        <a href="index.php" style="border: none; color: var(--muted-gray); font-size: 13px;">&larr; Return to Main Page</a>
    </div>
</div>

<?php include 'footer.php'; ?>