<?php
require_once '../Include/db.php'; // Include database connection

// Create a database instance
$dbInstance = new Database();
$conn = $dbInstance->getConnection();

// Fetch all teams with manager and field information
$query = "
    SELECT 
        t.TeamID,
        t.TeamName,
        l.Name AS LeagueName,
        u.Firstname AS ManagerFirstName,
        u.Surname AS ManagerSurname,
        f.Name AS FieldName,
        f.Location AS FieldLocation
    FROM 
        Team t
    LEFT JOIN 
        League l ON t.LeagueID = l.LeagueID
    LEFT JOIN 
        TeamManager tm ON t.ManagerID = tm.ManagerID
    LEFT JOIN 
        User u ON tm.UserID = u.UserID
    LEFT JOIN 
        Field f ON t.FieldID = f.FieldID
    ORDER BY 
        t.TeamName
";
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Teams</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>Team list</h2>
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
            <a href="Teams.php" class="stayOnPageLink">
                <i class="fa-solid fa-square-check"></i>Teams
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Matches.php">
                <i class="fa-solid fa-calendar"></i>Matches
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Pitches.php">
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
        <h2>Teams List</h2>
        <div class="users-container">
            <table class="users-table">
                <tr>
                    <th>Team ID</th>
                    <th>Team Name</th>
                    <th>League</th>
                    <th>Manager</th>
                    <th>Home Field</th>
                    <th>Field Location</th>
                </tr>
                <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['TeamID']); ?></td>
                        <td><?php echo htmlspecialchars($row['TeamName']); ?></td>
                        <td><?php echo htmlspecialchars($row['LeagueName']); ?></td>
                        <td>
                            <?php 
                            if ($row['ManagerFirstName']) {
                                echo htmlspecialchars($row['ManagerFirstName'] . ' ' . $row['ManagerSurname']);
                            } else {
                                echo 'No Manager';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['FieldName']); ?></td>
                        <td><?php echo htmlspecialchars($row['FieldLocation']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <!-- Add Team Button -->
        <a href="AddTeam.php" class="add-user-btn">+ Add Team</a>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>

    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
        
        function preventPageRefresh() {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
                    e.preventDefault();
                }
            });
        }
    </script>
</body>
</html> 