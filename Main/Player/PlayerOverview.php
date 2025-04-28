<?php
// PlayerOverview.php - Optimized for new database schema
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../Include/db.php';
try {
    $dbInstance = new Database();
    $conn = $dbInstance->getConnection();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

// Initialize profile display
$userDisplay = ['name' => 'Guest', 'role' => 'Not Logged In'];


<?php
try {
    session_start();
    $dbInstance = new Database();
    $db = $dbInstance->getConnection();
    
    // 1. Get user profile info
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("
            SELECT u.UserID, u.Firstname, u.Surname, u.AccountType, 
                   t.TeamName, tm.ManagerID
            FROM User u
            LEFT JOIN Player p ON u.UserID = p.UserID
            LEFT JOIN Team t ON p.TeamID = t.TeamID
            LEFT JOIN TeamManager tm ON u.UserID = tm.UserID
            WHERE u.UserID = :user_id
        ");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($user) {
            $userDisplay['name'] = $user['Firstname'] . ' ' . $user['Surname'];
            switch (strtolower($user['AccountType'])) {
                case 'player':
                    $userDisplay['role'] = "Player" . ($user['TeamName'] ? " - " . $user['TeamName'] : "");
                    break;
                case 'team manager':
                    if ($user['ManagerID']) {
                        $stmt = $db->prepare("SELECT TeamName FROM Team WHERE ManagerID = :manager_id");
                        $stmt->bindValue(':manager_id', $user['ManagerID'], SQLITE3_INTEGER);
                        $team = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                        $userDisplay['role'] = "Team Manager" . ($team['TeamName'] ? " - " . $team['TeamName'] : "");
                    }
                    break;
                case 'referee':
                    $userDisplay['role'] = "Referee";
                    break;
                case 'admin':
                    $userDisplay['role'] = "Administrator";
                    break;
                default:
                    $userDisplay['role'] = ucfirst($user['AccountType']);
            }
        }
    }

    // 2. Get player data
    $playerId = $_SESSION['player_id'] ?? 1;
    $stmt = $db->prepare("
        SELECT p.*, u.Firstname, u.Surname, t.TeamName, t.TeamID, t.LeagueID 
        FROM Player p
        JOIN User u ON p.UserID = u.UserID
        JOIN Team t ON p.TeamID = t.TeamID
        WHERE p.PlayerID = :playerId
    ");
    $stmt->bindValue(':playerId', $playerId, SQLITE3_INTEGER);
    $player = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if (!$player) throw new Exception("Player not found");

    // 3. Get team roster
    $teamPlayers = [];
    $stmt = $db->prepare("
        SELECT p.PlayerID, u.Firstname, u.Surname, p.Goals, p.Assists, 
               p.YellowCards, p.RedCards, p.Appearances
        FROM Player p
        JOIN User u ON p.UserID = u.UserID
        WHERE p.TeamID = :teamId
        ORDER BY p.Goals DESC
    ");
    $stmt->bindValue(':teamId', $player['TeamID'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['PlayerName'] = $row['Firstname'] . ' ' . $row['Surname'];
        $teamPlayers[] = $row;
    }

    // 4. Get matches using new schema
    $matches = [];
    $stmt = $db->prepare("
        SELECT m.*,
               CASE WHEN m.HomeTeamID = :teamId THEN m.HomeGoals ELSE m.AwayGoals END as TeamGoals,
               CASE WHEN m.HomeTeamID = :teamId THEN m.AwayGoals ELSE m.HomeGoals END as OpponentGoals
        FROM (
            SELECT 'League' as MatchType, lm.*, 
                   ht.TeamName as HomeTeam, at.TeamName as AwayTeam,
                   f.Name as Venue
            FROM League_Match lm
            JOIN Team ht ON lm.HomeTeamID = ht.TeamID
            JOIN Team at ON lm.AwayTeamID = at.TeamID
            JOIN Field f ON lm.FieldID = f.FieldID
            WHERE (lm.HomeTeamID = :teamId OR lm.AwayTeamID = :teamId)
            
            UNION
            
            SELECT 'Friendly' as MatchType, fm.*,
                   t1.TeamName as HomeTeam, t2.TeamName as AwayTeam,
                   f.Name as Venue
            FROM Friendly_Match fm
            JOIN Team t1 ON fm.TeamID = t1.TeamID
            JOIN Team t2 ON fm.OpposingTeamID = t2.TeamID
            JOIN Field f ON fm.FieldID = f.FieldID
            WHERE (fm.TeamID = :teamId OR fm.OpposingTeamID = :teamId)
        ) m
        ORDER BY m.MatchDate DESC
        LIMIT 10
    ");
    $stmt->bindValue(':teamId', $player['TeamID'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $matches[] = $row;
    }

    // Split matches
    $now = date('Y-m-d H:i:s');
    $upcoming = array_filter($matches, fn($m) => $m['MatchDate'] > $now);
    $recent = array_filter($matches, fn($m) => $m['MatchDate'] <= $now);

    // 5. Get league standings from new tables
    $leagueStandings = [];
    if ($player['LeagueID'] == 1) {
        $stmt = $db->prepare("
            SELECT t.TeamName, p.* 
            FROM Premier_League_Standings p
            JOIN Team t ON p.TeamID = t.TeamID
            ORDER BY p.Points DESC, p.GoalDifference DESC
        ");
    } else {
        $stmt = $db->prepare("
            SELECT t.TeamName, p.* 
            FROM La_Liga_Standings p
            JOIN Team t ON p.TeamID = t.TeamID
            ORDER BY p.Points DESC, p.GoalDifference DESC
        ");
    }
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $leagueStandings[] = $row;
    }

} catch (Exception $e) {
    die("Error loading player data: " . $e->getMessage());
} finally {
    if (isset($dbInstance)) $dbInstance->closeConnection();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Overview</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
        :root {
            --primary-color: #03588C;
            --secondary-color: #022340;
            --accent-color: #4fc3f7;
            --text-light: #F2F2F2;
            --text-muted: #b3e5fc;
            --card-bg: linear-gradient(135deg, #1b3d55, #022340);
        }
        
        .highlight {
            background-color: var(--primary-color);
            font-weight: bold;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-auto-rows: minmax(150px, auto);
            gap: 20px;
            padding: 20px;
            height: calc(100vh - 120px);
        }
        
        .card {
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 2px solid var(--primary-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .card h2 {
            color: var(--text-light);
            font-size: 1.3rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        /* Player Stats Grid */
        #player-stats {
            grid-column: span 3;
            grid-row: span 1;
            min-height: 320px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 10px;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.8rem;
            color: var(--accent-color);
            font-weight: bold;
            margin: 5px 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        /* Team Roster */
        #team-roster {
            grid-column: span 3;
            grid-row: span 1;
            min-height: 320px;
        }
        
        .roster-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .roster-table th {
            background-color: var(--primary-color);
            padding: 10px;
            text-align: left;
        }
        
        .roster-table td {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Other Cards */
        #standings { 
            grid-column: span 3;
            grid-row: span 2;
        }
        
        #recent-matches {
            grid-column: span 3;
            grid-row: span 1;
        }
        
        #fixtures {
            grid-column: 4 / 13;
            grid-row: 1 / 3;
        }
        
        .scrollable-container {
            overflow-y: auto;
            max-height: calc(100% - 40px);
        }
        
        /* Match Items */
        .match-item {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .match-opponent {
            font-weight: bold;
        }
        
        .match-date {
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .dashboard {
                grid-template-columns: 1fr 1fr;
            }
            
            #fixtures {
                grid-column: span 2;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .card {
                grid-column: span 1 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="profile-box">
            <i class="fa fa-user"></i>
            <div>
                <div class="name"><?= htmlspecialchars($userDisplay['name']) ?></div>
                <div class="role"><?= htmlspecialchars($userDisplay['role']) ?></div>
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
            <a href="TeamOverview.html" class="stayOnPageLink">
                <i class="fa-solid fa-people-group"></i> Player Overview
            </a>
        </div>
        <div class="sidebar-toolbox-container">
            <div class="sidebar-separator"></div>
            <div class="sidebar-toolbox-button">
                <a href="../Homepages/SettingsPersonal.html">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
            </div>
            <div class="sidebar-toolbox-button">
                <a href="../Create Account and Login/Login.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                </a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h1>Player Overview</h1>
        <div class="dashboard">
            <!-- Player Stats Card -->
            <div class="card" id="player-stats">
                <h2>MY STATS</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-label">Appearances</div>
                        <div class="stat-value"><?= $player['Appearances'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Goals</div>
                        <div class="stat-value"><?= $player['Goals'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assists</div>
                        <div class="stat-value"><?= $player['Assists'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Yellow Cards</div>
                        <div class="stat-value"><?= $player['YellowCards'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Red Cards</div>
                        <div class="stat-value"><?= $player['RedCards'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Team</div>
                        <div class="stat-value"><?= htmlspecialchars($player['TeamName']) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Team Roster Card -->
            <div class="card" id="team-roster">
                <h2>TEAM ROSTER</h2>
                <div class="scrollable-container">
                    <table class="roster-table">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Goals</th>
                                <th>Assists</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($teamPlayers, 0, 6) as $tp): ?>
                            <tr <?= $tp['PlayerID'] == $playerId ? 'class="highlight"' : '' ?>>
                                <td><?= htmlspecialchars($tp['PlayerName']) ?></td>
                                <td><?= $tp['Goals'] ?></td>
                                <td><?= $tp['Assists'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Matches Card -->
            <div class="card" id="recent-matches">
                <h2>RECENT MATCHES</h2>
                <div class="scrollable-container">
                    <?php foreach (array_slice($recent, 0, 5) as $match): 
                        $isHome = $match['HomeTeam'] == $player['TeamName'];
                        $opponent = $isHome ? $match['AwayTeam'] : $match['HomeTeam'];
                        $resultClass = '';
                        if ($match['TeamGoals'] > $match['OpponentGoals']) {
                            $resultClass = 'win';
                        } elseif ($match['TeamGoals'] == $match['OpponentGoals']) {
                            $resultClass = 'draw';
                        } else {
                            $resultClass = 'lose';
                        }
                    ?>
                    <div class="match-item">
                        <div class="match-opponent">vs <?= htmlspecialchars($opponent) ?></div>
                        <div class="match-score <?= $resultClass ?>">
                            <?= $isHome ? $match['TeamGoals'] : $match['OpponentGoals'] ?>
                            - 
                            <?= $isHome ? $match['OpponentGoals'] : $match['TeamGoals'] ?>
                        </div>
                        <div class="match-date">
                            <?= date('M j, Y', strtotime($match['MatchDate'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- League Standings Card -->
            <div class="card" id="standings">
                <h2>LEAGUE STANDINGS</h2>
                <div class="scrollable-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Pos</th>
                                <th>Team</th>
                                <th>Pld</th>
                                <th>GD</th>
                                <th>Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leagueStandings as $index => $team): ?>
                            <tr <?= $team['TeamName'] == $player['TeamName'] ? 'class="highlight"' : '' ?>>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($team['TeamName']) ?></td>
                                <td><?= $team['GamesPlayed'] ?></td>
                                <td><?= ($team['GoalDifference'] > 0 ? '+' : '') . $team['GoalDifference'] ?></td>
                                <td><?= $team['Points'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Upcoming Fixtures Card -->
            <div class="card" id="fixtures">
                <h2>UPCOMING FIXTURES</h2>
                <div class="scrollable-container">
                    <?php foreach (array_slice($upcoming, 0, 5) as $match): ?>
                    <div class="match-item">
                        <div class="match-opponent"><?= htmlspecialchars($match['HomeTeam'] . ' vs ' . $match['AwayTeam']) ?></div>
                        <div class="match-date">
                            <i class="far fa-calendar"></i> 
                            <?= date('M j, Y', strtotime($match['MatchDate'])) ?>
                            <i class="far fa-clock"></i>
                            <?= date('H:i', strtotime($match['MatchDate'])) ?>
                        </div>
                        <div class="match-venue">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?= htmlspecialchars($match['Venue']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>