<?php
require_once '../Include/db.php'; 

$dbInstance = new Database();
$conn = $dbInstance->getConnection();

// Handle delete action
if (isset($_GET['delete_id']) && isset($_GET['type'])) {
    $deleteId = $_GET['delete_id'];
    $matchType = $_GET['type'];
    
    try {
        $conn->exec("BEGIN TRANSACTION");
        
        if ($matchType === 'league') {
            // Delete referee bookings first
            $deleteRefereeQuery = "DELETE FROM Referee_Booking WHERE LeagueMatchID = :matchId";
            $stmt = $conn->prepare($deleteRefereeQuery);
            $stmt->bindValue(':matchId', $deleteId, SQLITE3_INTEGER);
            $stmt->execute();
            
            // Then delete the match
            $deleteQuery = "DELETE FROM League_Match WHERE LeagueMatchID = :matchId";
        } else {
            // Delete referee bookings first
            $deleteRefereeQuery = "DELETE FROM Referee_Booking WHERE FriendlyMatchID = :matchId";
            $stmt = $conn->prepare($deleteRefereeQuery);
            $stmt->bindValue(':matchId', $deleteId, SQLITE3_INTEGER);
            $stmt->execute();
            
            // Then delete the match
            $deleteQuery = "DELETE FROM Friendly_Match WHERE FriendlyMatchID = :matchId";
        }
        
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bindValue(':matchId', $deleteId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $conn->exec("COMMIT");
        
        if ($result) {
            header("Location: Matches.php?deleted=1");
            exit();
        } else {
            header("Location: Matches.php?deleted=0");
            exit();
        }
    } catch (Exception $e) {
        $conn->exec("ROLLBACK");
        header("Location: Matches.php?deleted=0");
        exit();
    }
}

// Pagination setup
$results_per_page = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Get total number of matches
$countLeagueQuery = "SELECT COUNT(*) as total FROM League_Match";
$countFriendlyQuery = "SELECT COUNT(*) as total FROM Friendly_Match";

$leagueCount = $conn->query($countLeagueQuery)->fetchArray(SQLITE3_ASSOC)['total'];
$friendlyCount = $conn->query($countFriendlyQuery)->fetchArray(SQLITE3_ASSOC)['total'];
$total_rows = $leagueCount + $friendlyCount;
$total_pages = ceil($total_rows / $results_per_page);

// Fetch matches with pagination 
$query = "
    SELECT * FROM (
        SELECT 
            lm.LeagueMatchID AS MatchID,
            'League' AS MatchType,
            l.Name AS LeagueName,
            ht.TeamName AS HomeTeam,
            at.TeamName AS AwayTeam,
            lm.MatchDate,
            lm.Venue,
            lm.Status,
            NULL AS FieldName,
            NULL AS PricePerHour
        FROM League_Match lm
        JOIN League l ON lm.LeagueID = l.LeagueID
        JOIN Team ht ON lm.HomeTeamID = ht.TeamID
        JOIN Team at ON lm.AwayTeamID = at.TeamID
        
        UNION ALL
        
        SELECT 
            fm.FriendlyMatchID AS MatchID,
            'Friendly' AS MatchType,
            NULL AS LeagueName,
            t1.TeamName AS HomeTeam,
            t2.TeamName AS AwayTeam,
            fm.MatchDate,
            f.Name AS Venue,
            fm.Status,
            f.Name AS FieldName,
            f.PricePerHour
        FROM Friendly_Match fm
        JOIN Team t1 ON fm.TeamID = t1.TeamID
        JOIN Team t2 ON fm.OpposingTeamID = t2.TeamID
        JOIN Field f ON fm.FieldID = f.FieldID
    ) AS AllMatches
    ORDER BY MatchDate DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':limit', $results_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

$allMatches = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $allMatches[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Matches</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .user-content {
            position: relative;
            margin-left: 250px;
            padding: 20px;
            min-height: calc(100vh - 160px);
            background-color: #022340;
            color: #F2F2F2;
            font-family: Verdana, Tahoma, sans-serif;
            overflow-x: auto;
        }

        .matches-container {
            margin: 20px 0;
            overflow-x: auto;
        }

        .matches-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .matches-table th, .matches-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #1e4e75;
        }

        .matches-table th {
            background-color: #03588C;
            color: white;
            font-weight: 600;
        }

        .matches-table tr:hover {
            background-color: rgba(3, 88, 140, 0.2);
        }

        .status-scheduled {
            color: #3498db;
        }
        
        .status-ongoing {
            color: #2ecc71;
            font-weight: bold;
        }
        
        .status-completed {
            color: #95a5a6;
        }
        
        .status-cancelled {
            color: #e74c3c;
            text-decoration: line-through;
        }

        .add-match-btn {
            display: inline-block;
            padding: 10px 15px;
            background: linear-gradient(90deg, #00c4ff, #0059a8);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            margin: 20px 0;
            transition: background 0.3s ease;
        }

        .add-match-btn:hover {
            background: linear-gradient(90deg, #009ecf, #004b91);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #1e4e75;
            margin: 0 4px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .pagination a.active {
            background-color: #00c4ff;
            color: white;
            border: 1px solid #00c4ff;
        }

        .pagination a:hover:not(.active) {
            background-color: #03588C;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            display: none;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }

        .success {
            background-color: #2ecc71;
        }

        .error {
            background-color: #e74c3c;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        @keyframes fadeOut {
            from {opacity: 1;}
            to {opacity: 0;}
        }

        .no-data {
            color: #F2F2F2;
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>Match list</h2>
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
        <?php 
        // Show notification if deleted
        if (isset($_GET['deleted'])) {
            if ($_GET['deleted'] == 1) {
                echo '<div class="notification success" id="notification">Match deleted successfully!</div>';
            } else {
                echo '<div class="notification error" id="notification">Error deleting match!</div>';
            }
        }
        ?>
        
        <h2>Matches List</h2>
        <div class="matches-container">
            <table class="matches-table">
                <tr>
                    <th>Type</th>
                    <th>League</th>
                    <th>Home Team</th>
                    <th>Away Team</th>
                    <th>Date & Time</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>Field</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
                <?php 
                $hasRows = false;
                foreach ($allMatches as $row): 
                    $hasRows = true;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['MatchType']); ?></td>
                        <td><?php echo htmlspecialchars($row['LeagueName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['HomeTeam']); ?></td>
                        <td><?php echo htmlspecialchars($row['AwayTeam']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['MatchDate']))); ?></td>
                        <td><?php echo htmlspecialchars($row['Venue']); ?></td>
                        <td class="status-<?php echo strtolower($row['Status']); ?>">
                            <?php echo htmlspecialchars($row['Status']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['FieldName'] ?? 'N/A'); ?></td>
                        <td><?php echo isset($row['PricePerHour']) ? '$' . htmlspecialchars($row['PricePerHour']) : 'N/A'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="EditMatch.php?id=<?php echo $row['MatchID']; ?>&type=<?php echo strtolower($row['MatchType']); ?>" class="edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="#" class="delete-btn" 
                                   onclick="confirmDelete(<?php echo $row['MatchID']; ?>, '<?php echo strtolower($row['MatchType']); ?>')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (!$hasRows): ?>
                    <tr>
                        <td colspan="10" class="no-data">No matches found in the database</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="Matches.php?page=<?php echo $page - 1; ?>">&laquo;</a>
            <?php endif; ?>

            <?php 
            // Show page numbers
            $visible_pages = 5;
            $start = max(1, $page - floor($visible_pages / 2));
            $end = min($total_pages, $start + $visible_pages - 1);
            
            for ($i = $start; $i <= $end; $i++): ?>
                <a href="Matches.php?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="Matches.php?page=<?php echo $page + 1; ?>">&raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Add Match Button -->
        <a href="AddMatch.php" class="add-match-btn">+ Add Match</a>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>

    <script src="../sidebar.js"></script>
    <script>
        window.onload = function() {
            preventPageRefresh();
            
            // Show notification if it exists
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }
        };
        
        function confirmDelete(matchId, matchType) {
            if (confirm('Are you sure you want to delete this match? This action cannot be undone.')) {
                window.location.href = 'Matches.php?delete_id=' + matchId + '&type=' + matchType;
            }
        }
        
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