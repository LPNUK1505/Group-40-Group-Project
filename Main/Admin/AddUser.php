<?php
session_start();

// Check if user is logged in - ADDED MISSING SESSION CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Create Account and Login/Login.php");
    exit();
}

require_once '../Include/db.php';

// Get user info - ADDED USER INFO FETCHING
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'Admin';

// Database connection - CHANGED TO USE $conn CONSISTENTLY
$dbInstance = new Database();
$conn = $dbInstance->getConnection();

// Get user details - ADDED USER DETAILS QUERY
$userQuery = $conn->prepare("SELECT Firstname, Surname FROM User WHERE UserID = ?");
$userQuery->bindValue(1, $user_id, SQLITE3_INTEGER);
$userResult = $userQuery->execute();
$userData = $userResult->fetchArray();

$userDisplay = [
    'name' => $userData ? htmlspecialchars($userData['Firstname'] . ' ' . $userData['Surname']) : 'Admin User',
    'role' => htmlspecialchars($user_role)
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .user-buttons {
            margin: 40px;
            display: flex;
            gap: 20px;
        }

        .user-buttons a {
            text-decoration: none;
            background-color: #0e76a8;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .user-buttons a:hover {
            background-color: #084c70;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h2>Add User</h2>
        <div class="profile-box">
            <i class="fa fa-user"></i>
            <div>
                <div class="name"><?= htmlspecialchars($userDisplay['name']) ?></div>
                <div class="role"><?= htmlspecialchars($userDisplay['role']) ?></div>
            </div>
            <i class="fa fa-chevron-down dropdown-icon"></i>
            <div class="dropdown">
                <a href="#">Profile</a>
                <a href="../Homepages/SettingsData.php">Manage Data</a>
                <a href="../Create Account and Login/Login.php">Sign Out</a>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">
        <div class="sidebar-separator"></div>
        <div class="sidebar-button">
            <a href="Users.php">
                <i class="fa-solid fa-arrow-left"></i>Go Back
            </a>
        </div>

        <div class="sidebar-toolbox-container">
            <div class="sidebar-separator"></div>
            <div class="sidebar-toolbox-button">
                <a href="../Homepages/SettingsPersonal.php">
                    <i class="fa-solid fa-gear"></i>Settings
                </a>
            </div>
            <div class="sidebar-toolbox-button">
                <a href="../Create Account and Login/Login.php">
                    <i class="fa-solid fa-right-from-bracket"></i>Sign Out
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="user-buttons">
            <a href="AddPlayer.php">+ Add Player</a>
            <a href="AddReferee.php">+ Add Referee</a>
            <a href="AddManager.php">+ Add Manager</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>

    <!-- Scripts -->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</body>
</html>
