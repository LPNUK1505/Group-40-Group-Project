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
            <a href="Users.php">
                <i class="fa-solid fa-arrow-left"></i>Go Back
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
    <div class="main-content">
        <div class="user-buttons">
            <a href="AddPlayer.php">+ Add Player</a>
            <a href="AddReferee.php">+ Add Referee</a>
            <a href="AddManager.php">+ Add Manager</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>

    <!-- Scripts -->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</body>
</html>
