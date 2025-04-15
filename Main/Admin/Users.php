<?php
require_once '../Include/db.php'; // Include database connection

// Create a database instance
$dbInstance = new Database();
$conn = $dbInstance->getConnection();

// Fetch all users from the User table
$query = "SELECT UserID, RoleID, AccountType, Firstname, Surname, Username, DateOfBirth, Nationality, PhoneNumber, Email FROM User";
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>User list</h2>
        <div class="profile-box">
            <i class="fa fa-user"></i>
            <div>
                <div class="name">Fazley</div>
                <div class="role">Admin</div>
            </div>
            <i class="fa fa-chevron-down dropdown-icon"></i>
            <div class="dropdown">
                <a href="#">Profile</a>
                <a href="../Homepages/SettingsData.html">Manage Data</a>
                <a href="../Create Account and Login/Login.php">Sign Out</a>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">
        <div class="sidebar-separator"></div>
        <div class="sidebar-button">
            <a href="Dashboard.php">
                <i class="fa-solid fa-people-group"></i>Dashboard
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Users.php">
                <i class="fa-solid fa-user-plus"></i>Users
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Teams.php">
                <i class="fa-solid fa-square-check"></i>Teams
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Matches.php">
                <i class="fa-solid fa-calendar"></i>Matches
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Pitches.php" class="stayOnPageLink">
                <i class="fa-solid fa-chart-simple"></i>Pitches
            </a>
        </div>
        <div class="sidebar-toolbox-container">
            <div class="sidebar-separator"></div>
            <div class="sidebar-toolbox-button">
                <a href="../Homepages/SettingsPersonal.html">
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
    <div class="user-content">
        <h2>Users List</h2>
        <div class="users-container">
            <table class="users-table">
                <tr>
                    <th>UserID</th>
                    <th>RoleID</th>
                    <th>Account Type</th>
                    <th>First Name</th>
                    <th>Surname</th>
                    <th>Username</th>
                    <th>Date of Birth</th>
                    <th>Nationality</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                </tr>
                <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['UserID']); ?></td>
                        <td><?php echo htmlspecialchars($row['RoleID']); ?></td>
                        <td><?php echo htmlspecialchars($row['AccountType']); ?></td>
                        <td><?php echo htmlspecialchars($row['Firstname']); ?></td>
                        <td><?php echo htmlspecialchars($row['Surname']); ?></td>
                        <td><?php echo htmlspecialchars($row['Username']); ?></td>
                        <td><?php echo htmlspecialchars($row['DateOfBirth']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nationality']); ?></td>
                        <td><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <!-- Add User Button -->
        <a href="AddUser.php" class="add-user-btn">+ Add User</a>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>

    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</body>
</html>
