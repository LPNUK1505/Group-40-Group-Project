<?php
require_once '../Include/db.php';

$message = "";

// Only process the form if it was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username    = $_POST['username'];
    $password    = $_POST['password'];
    $firstname   = $_POST['firstname'];
    $lastname    = $_POST['lastname'];
    $email       = $_POST['email'];
    $role        = $_POST['role'];
    $nationality = $_POST['nationality'];
    $phoneNumber = $_POST['phonenumber'] ?? '';
    $dateOfBirth = $_POST['dateofbirth'] ?? '2000-01-01';

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Get DB connection
        $dbInstance = new Database();
        $conn = $dbInstance->getConnection();

        // Prepare the insert statement
        $stmt = $conn->prepare("
            INSERT INTO User 
            (Username, Password, Firstname, Surname, Email, AccountType, Nationality, PhoneNumber, DateOfBirth, RoleID)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Map role to RoleID (adjust these IDs based on your Roles table)
        $roleIdMap = [
            'admin' => 1,
            'player' => 2,
            'team_manager' => 3,
            'referee' => 4
        ];
        $roleId = $roleIdMap[$role] ?? 2; // Default to player if not found

        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $stmt->bindValue(2, $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(3, $firstname, SQLITE3_TEXT);
        $stmt->bindValue(4, $lastname, SQLITE3_TEXT);
        $stmt->bindValue(5, $email, SQLITE3_TEXT);
        $stmt->bindValue(6, ucfirst($role), SQLITE3_TEXT);
        $stmt->bindValue(7, $nationality, SQLITE3_TEXT);
        $stmt->bindValue(8, $phoneNumber, SQLITE3_TEXT);
        $stmt->bindValue(9, $dateOfBirth, SQLITE3_TEXT);
        $stmt->bindValue(10, $roleId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            // Close connection before redirecting
            $dbInstance->closeConnection();
            
            // Redirect to login page
            header("Location: Login.php");
            exit();
        } else {
            $message = "Failed to create account: " . $conn->lastErrorMsg();
        }

        $dbInstance->closeConnection();

    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<div class="header"></div>
<div class="sidebar">
    <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">
    <div class="sidebar-separator"></div>
</div>
<div class="footer">
    <a href="AboutUs.html" class="stayOnPageLink">About Us</a>
    <a href="ContactUs.html">Contact Us</a>
</div>

<div class="main-content">
    <h1>Create Account</h1>
    <?php if (!empty($message)) echo "<p style='color: red;'>$message</p>"; ?>
    <form method="post" id="create-account-form">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="firstname" placeholder="First Name" required>
        <input type="text" name="lastname" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="nationality" placeholder="Nationality" required>
        <input type="tel" name="phonenumber" placeholder="Phone Number" required>
        <input type="date" name="dateofbirth" placeholder="Date of Birth" required>
        <select name="role" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="player">Player</option>
            <option value="team_manager">Team Manager</option>
            <option value="referee">Referee</option>
        </select>
        <button type="submit">Create Account</button>
    </form>
</div>

</body>
</html>