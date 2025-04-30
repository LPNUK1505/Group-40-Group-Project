<?php
session_start();
require_once '../Include/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $dbInstance = new Database();
        $conn = $dbInstance->getConnection();

        $stmt = $conn->prepare("SELECT UserID, Username, Password, AccountType FROM User WHERE Username = ?");
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user) {
            // Check if password is hashed
            if (password_verify($password, $user['Password'])) {
                // Password is correct (hashed)
                loginUser($user);
            } 
            // Temporary transition check might remove maybe?
            elseif ($password === $user['Password']) {
                // Plaintext match 
                loginUser($user);
                
                // Optional: Upgrade to hashed password in the future if we are ready
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $upgradeStmt = $conn->prepare("UPDATE User SET Password = ? WHERE UserID = ?");
                $upgradeStmt->bindValue(1, $hashed, SQLITE3_TEXT);
                $upgradeStmt->bindValue(2, $user['UserID'], SQLITE3_INTEGER);
                $upgradeStmt->execute();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['username'] = $user['Username'];
    $_SESSION['account_type'] = $user['AccountType'];
    
    // Redirect based on account type
    switch (strtolower($user['AccountType'])) {
        case 'admin':
            header("Location: ../Admin/Dashboard.php");
            break;
        case 'team manager':
            header("Location: ../Team Manager/TeamOverview.php");
            break;
        case 'player':
            header("Location: ../Player/PlayerOverview.php");
            break;
        case 'referee':
            header("Location: ../refereePages/refereeDashboard.php");
            break;
        default:
            header("Location: ../index.php");
    }
    exit();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<div class="header"></div>
<div class="sidebar">
    <img src="../GoIkonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">
    <div class="sidebar-separator"></div>
</div>
<div class="footer">
    <a href="AboutUs.php" class="stayOnPageLink">About Us</a>
    <a href="ContactUs.php">Contact Us</a>
</div>

<div class="main-content">
    <h1>Login</h1>
    <form method="post" id="login-form">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <?php if (!empty($error)): ?>
            <p style='color:red;'><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <button type="submit">Login</button>
        <p><a href="CreateAccount.php" class="create-account">Create Account</a></p>
    </form>
</div>

</body>
</html>