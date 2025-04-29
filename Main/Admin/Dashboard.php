<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Create Account and Login/Login.php");
    exit();
}

require_once '../Include/db.php';

// Get user info
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'Admin';

// Database connection
$dbInstance = new Database();
$db = $dbInstance->getConnection();

// Get user details
$userQuery = $db->prepare("SELECT Firstname, Surname FROM User WHERE UserID = ?");
$userQuery->bindValue(1, $user_id, SQLITE3_INTEGER);
$userResult = $userQuery->execute();
$userData = $userResult->fetchArray();

$userDisplay = [
    'name' => $userData ? htmlspecialchars($userData['Firstname'] . ' ' . $userData['Surname']) : 'Admin User',
    'role' => htmlspecialchars($user_role)
];

// Pagination setup for matches
$matchesPerPage = 5;
$currentPage = isset($_GET['matches_page']) ? (int)$_GET['matches_page'] : 1;
$offset = ($currentPage - 1) * $matchesPerPage;

// Function to safely fetch counts
function getCount($db, $table, $condition = '') {
    $query = "SELECT COUNT(*) as count FROM $table";
    if ($condition) {
        $query .= " WHERE $condition";
    }
    $result = $db->query($query);
    return $result->fetchArray()['count'];
}

// Get all the counts we need
$totalUsers = getCount($db, 'User');
$totalTeams = getCount($db, 'Team');
$activeUsers = getCount($db, 'Activity', 'IsActive = 1');
$totalPlayers = getCount($db, 'Player');
$totalManagers = getCount($db, 'TeamManager');
$totalReferees = getCount($db, 'Referee');
$totalMatches = getCount($db, 'League_Match') + getCount($db, 'Friendly_Match');
$upcomingMatches = getCount($db, 'League_Match', "Status = 'Scheduled' AND MatchDate > datetime('now')") + 
                  getCount($db, 'Friendly_Match', "Status = 'Scheduled' AND MatchDate > datetime('now')");
$pendingMaintenance = getCount($db, 'Maintenance', "Status != 'Completed'");
$expiredCertificates = getCount($db, 'TeamManager_Certificate', "ExpiryDate < datetime('now')") + 
                      getCount($db, 'Referee_Certificate', "ExpiryDate < datetime('now')");

// Recent activities query
$recentActivities = $db->query("
    SELECT u.Firstname, u.Surname, a.LastLogin 
    FROM Activity a
    JOIN User u ON a.UserID = u.UserID
    ORDER BY a.LastLogin DESC
    LIMIT 5
");

// Get total matches count for pagination
$totalMatchesCount = getCount($db, 'League_Match') + getCount($db, 'Friendly_Match');

// Recent matches query with pagination
$recentMatches = $db->query("
    SELECT 
        'League' as MatchType,
        lm.LeagueMatchID as MatchID,
        t1.TeamName as HomeTeam,
        t2.TeamName as AwayTeam,
        lm.MatchDate,
        lm.Status
    FROM League_Match lm
    JOIN Team t1 ON lm.HomeTeamID = t1.TeamID
    JOIN Team t2 ON lm.AwayTeamID = t2.TeamID
    
    UNION ALL
    
    SELECT 
        'Friendly' as MatchType,
        fm.FriendlyMatchID as MatchID,
        t1.TeamName as HomeTeam,
        t2.TeamName as AwayTeam,
        fm.MatchDate,
        fm.Status
    FROM Friendly_Match fm
    JOIN Team t1 ON fm.TeamID = t1.TeamID
    JOIN Team t2 ON fm.OpposingTeamID = t2.TeamID
    
    ORDER BY MatchDate DESC
    LIMIT $matchesPerPage OFFSET $offset
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
    /* Dashboard-Specific Styles */
    .dashboard-page .main-content {
        background: #044D8C;
        color: #ffffff;
        overflow-y: auto;
        padding: 20px;
    }

    /* Metric Cards */
    .dashboard-metrics {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .metric-card {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        padding: 20px;
        flex: 1;
        min-width: 200px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: white;
    }

    .metric-label {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 5px;
    }

    .metric-icon {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 2rem;
        opacity: 0.3;
        color: white;
    }

    /* Dashboard Sections */
    .dashboard-wrapper {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .dashboard-section {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        flex: 1;
    }

    .dashboard-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        color: white;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 10px;
    }

    /* Overview Box */
    .overview-box {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .overview-item {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .overview-item.warning {
        background: rgba(255, 243, 205, 0.3);
        color: #ffcc00;
    }

    .overview-label {
        font-weight: bold;
    }

    /* Activities Box */
    .activities-box {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.1);
    }

    .activity-icon {
        font-size: 1.2rem;
        color: #4fc3f7;
    }

    .activity-details {
        display: flex;
        flex-direction: column;
    }

    .activity-user {
        font-weight: bold;
    }

    .activity-time {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.7);
    }

    /* Matches Table Container */
    .matches-table-container {
        width: 100%;
        overflow-x: auto;
        margin-bottom: 20px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
    }

    .matches-table {
        width: 100%;
        min-width: 600px;
        border-collapse: collapse;
        color: white;
    }

    .matches-table th {
        background: rgba(255, 255, 255, 0.15);
        padding: 12px 15px;
        text-align: left;
        font-weight: bold;
        position: sticky;
        top: 0;
    }

    .matches-table td {
        padding: 10px 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .matches-table tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    /* Status badges */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-badge.completed {
        background: rgba(76, 175, 80, 0.2);
        color: #4CAF50;
    }

    .status-badge.scheduled {
        background: rgba(33, 150, 243, 0.2);
        color: #2196F3;
    }

    .status-badge.ongoing {
        background: rgba(255, 193, 7, 0.2);
        color: #FFC107;
    }

    .status-badge.cancelled {
        background: rgba(244, 67, 54, 0.2);
        color: #F44336;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        gap: 10px;
    }

    .pagination a, .pagination span {
        padding: 8px 16px;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
    }

    .pagination a:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .pagination .current {
        background: rgba(255, 255, 255, 0.3);
        font-weight: bold;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .dashboard-wrapper {
            flex-direction: column;
        }
        
        .overview-box {
            grid-template-columns: 1fr;
        }
        
        .metric-card {
            min-width: 100%;
        }
        
        .matches-table-container {
            border-radius: 0;
            margin-left: -20px;
            margin-right: -20px;
            width: calc(100% + 40px);
        }
    }
    </style>
</head>
<body class="dashboard-page">
    <!-- Header -->
    <div class="header">
        <h1>Dashboard</h1>
        <div class="profile-box">
            <i class="fa fa-user"></i>
            <div>
                <div class="name"><?= $userDisplay['name'] ?></div>
                <div class="role"><?= $userDisplay['role'] ?></div>
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

        <!-- Sidebar Buttons -->
        <div class="sidebar-separator"></div>
        <div class="sidebar-button active">
            <a href="Dashboard.php">
            <i class="fa-solid fa-house"></i></i>Dashboard
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Users.php">
                <i class="fa-solid fa-user-plus"></i>Users
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Teams.php">
            <i class="fa-solid fa-people-group"></i></i>Teams
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Matches.php">
                <i class="fa-solid fa-calendar"></i>Matches
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Pitches.php">
            <i class="fa-solid fa-street-view"></i></i>Pitches
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
        <h2>Welcome, <?= $userDisplay['name'] ?>!</h2>

        <!-- Dashboard Metrics Section -->
        <div class="dashboard-metrics">
            <div class="metric-card">
                <div class="metric-value"><?php echo $totalUsers; ?></div>
                <div class="metric-label">Total Users</div>
                <i class="fa-solid fa-users metric-icon"></i>
            </div>
            
            <div class="metric-card">
                <div class="metric-value"><?php echo $totalTeams; ?></div>
                <div class="metric-label">Teams</div>
                <i class="fa-solid fa-people-group metric-icon"></i>
            </div>
            
            <div class="metric-card">
                <div class="metric-value"><?php echo $activeUsers; ?></div>
                <div class="metric-label">Active Users</div>
                <i class="fa-solid fa-user-check metric-icon"></i>
            </div>
            
            <div class="metric-card">
                <div class="metric-value"><?php echo $totalMatches; ?></div>
                <div class="metric-label">Total Matches</div>
                <i class="fa-solid fa-futbol metric-icon"></i>
            </div>
        </div>

        <div class="dashboard-wrapper">
            <!-- Overview Section -->
            <div class="dashboard-section">
                <h3>System Overview</h3>
                <div class="overview-box">
                    <div class="overview-item">
                        <span class="overview-label">Players:</span>
                        <span class="overview-value"><?php echo $totalPlayers; ?></span>
                    </div>
                    <div class="overview-item">
                        <span class="overview-label">Team Managers:</span>
                        <span class="overview-value"><?php echo $totalManagers; ?></span>
                    </div>
                    <div class="overview-item">
                        <span class="overview-label">Referees:</span>
                        <span class="overview-value"><?php echo $totalReferees; ?></span>
                    </div>
                    <div class="overview-item">
                        <span class="overview-label">Upcoming Matches:</span>
                        <span class="overview-value"><?php echo $upcomingMatches; ?></span>
                    </div>
                    <div class="overview-item warning">
                        <span class="overview-label">Pending Maintenance:</span>
                        <span class="overview-value"><?php echo $pendingMaintenance; ?></span>
                    </div>
                    <div class="overview-item warning">
                        <span class="overview-label">Expired Certificates:</span>
                        <span class="overview-value"><?php echo $expiredCertificates; ?></span>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Section -->
            <div class="dashboard-section">
                <h3>Recent Activities</h3>
                <div class="activities-box">
                    <?php while ($activity = $recentActivities->fetchArray()): ?>
                    <div class="activity-item">
                        <i class="fa-solid fa-user activity-icon"></i>
                        <div class="activity-details">
                            <span class="activity-user"><?php echo htmlspecialchars($activity['Firstname']) . ' ' . htmlspecialchars($activity['Surname']); ?></span>
                            <span class="activity-time">Last login: <?php echo date('M j, Y g:i A', strtotime($activity['LastLogin'])); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Recent Matches Section -->
        <div class="dashboard-section">
            <h3>Recent Matches</h3>
            <div class="matches-table-container">
                <table class="matches-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Teams</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($match = $recentMatches->fetchArray()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($match['MatchType']); ?></td>
                            <td><?php echo htmlspecialchars($match['HomeTeam']) . ' vs ' . htmlspecialchars($match['AwayTeam']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($match['MatchDate'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($match['Status']); ?>">
                                    <?php echo htmlspecialchars($match['Status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php
                $totalPages = ceil($totalMatchesCount / $matchesPerPage);
                
                // Previous button
                if ($currentPage > 1) {
                    echo '<a href="?matches_page='.($currentPage - 1).'">&laquo; Previous</a>';
                }
                
                // Page numbers
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                if ($startPage > 1) {
                    echo '<a href="?matches_page=1">1</a>';
                    if ($startPage > 2) echo '<span>...</span>';
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $currentPage) {
                        echo '<span class="current">'.$i.'</span>';
                    } else {
                        echo '<a href="?matches_page='.$i.'">'.$i.'</a>';
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) echo '<span>...</span>';
                    echo '<a href="?matches_page='.$totalPages.'">'.$totalPages.'</a>';
                }
                
                // Next button
                if ($currentPage < $totalPages) {
                    echo '<a href="?matches_page='.($currentPage + 1).'">Next &raquo;</a>';
                }
                ?>
            </div>
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