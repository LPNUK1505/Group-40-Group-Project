<?php
// Add memory limit at the very top
ini_set('memory_limit', '256M');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$userDisplay = ['name' => 'Guest', 'role' => 'Not Logged In'];
$player = [];
$teamPlayers = [];
$recent = [];
$upcoming = [];
$leagueStandings = [];
$dbInstance = null;

try {
    include_once __DIR__ . '/../Include/db.php';
    $dbInstance = new Database();
    $db = $dbInstance->getConnection();
    
    // Added SQLite performance optimisations
    $db->exec("PRAGMA journal_mode = WAL");
    $db->exec("PRAGMA synchronous = NORMAL");
    
    session_start();

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        // 1. Get user profile 
        $stmt = $db->prepare("
            SELECT u.UserID, u.Firstname, u.Surname, u.AccountType, 
                   t.TeamID, t.TeamName, t.LeagueID
            FROM User u
            LEFT JOIN Player p ON u.UserID = p.UserID
            LEFT JOIN Team t ON p.TeamID = t.TeamID
            WHERE u.UserID = :user_id
            LIMIT 1
        ");
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($user) {
            $userDisplay['name'] = $user['Firstname'] . ' ' . $user['Surname'];
            $userDisplay['role'] = ucfirst($user['AccountType']);
            
            if (strtolower($user['AccountType']) === 'player' && !empty($user['TeamName'])) {
                $userDisplay['role'] .= " - " . $user['TeamName'];
            }

            // 2. Get player stats 
            if (strtolower($user['AccountType']) === 'player') {
                $stmt = $db->prepare("
                    SELECT Appearances, Goals, Assists, YellowCards, RedCards, TeamName 
                    FROM Player p
                    JOIN Team t ON p.TeamID = t.TeamID 
                    WHERE p.UserID = :user_id
                    LIMIT 1
                ");
                $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
                $player = $stmt->execute()->fetchArray(SQLITE3_ASSOC) ?: [];

                // 3. Get team roster 
                $stmt = $db->prepare("
                    SELECT p.PlayerID, u.Firstname || ' ' || u.Surname AS PlayerName, 
                           p.Goals, p.Assists
                    FROM Player p
                    JOIN User u ON p.UserID = u.UserID
                    WHERE p.TeamID = :team_id
                    ORDER BY p.Goals DESC
                    LIMIT 15
                ");
                $stmt->bindValue(':team_id', $user['TeamID'], SQLITE3_INTEGER);
                $result = $stmt->execute();
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $teamPlayers[] = $row;
                }

                // 4. Get recent matches 
                $stmt = $db->prepare("
                    SELECT 
                        t1.TeamName AS HomeTeam, 
                        t2.TeamName AS AwayTeam,
                        lm.MatchDate,
                        lm.HomeGoals,
                        lm.AwayGoals
                    FROM League_Match lm
                    JOIN Team t1 ON lm.HomeTeamID = t1.TeamID
                    JOIN Team t2 ON lm.AwayTeamID = t2.TeamID
                    WHERE (t1.TeamID = :team_id OR t2.TeamID = :team_id)
                    AND lm.Status = 'Completed'
                    ORDER BY lm.MatchDate DESC
                    LIMIT 5
                ");
                $stmt->bindValue(':team_id', $user['TeamID'], SQLITE3_INTEGER);
                $result = $stmt->execute();
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $recent[] = $row;
                }

    // upcoming matches 
    $upcoming = []; // Initialise empty array

    if (!empty($user['TeamID'])) {
        $stmt = $db->prepare("
            SELECT 
                t1.TeamName AS HomeTeam,
                t2.TeamName AS AwayTeam,
                lm.MatchDate,
                f.Name AS Venue
            FROM League_Match lm
            JOIN Team t1 ON lm.HomeTeamID = t1.TeamID
            JOIN Team t2 ON lm.AwayTeamID = t2.TeamID
            LEFT JOIN Field f ON lm.FieldID = f.FieldID
            WHERE (t1.TeamID = :team_id OR t2.TeamID = :team_id)
            AND Status = 'Scheduled'
            ORDER BY lm.MatchDate ASC
            LIMIT 5
        ");
        $stmt->bindValue(':team_id', $user['TeamID'], SQLITE3_INTEGER);
        
        $result = $stmt->execute();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $upcoming[] = $row;
        }
    }
                // league standings
                $leagueTable = ($user['LeagueID'] == 1) ? 'Premier_League_Standings' : 'La_Liga_Standings';
                $stmt = $db->prepare("
                    SELECT TeamName, Points, GoalDifference, GamesPlayed
                    FROM {$leagueTable} s
                    JOIN Team t ON s.TeamID = t.TeamID
                    WHERE t.LeagueID = :league_id
                    ORDER BY s.Points DESC
                    LIMIT 10
                ");
                $stmt->bindValue(':league_id', $user['LeagueID'], SQLITE3_INTEGER);
                $result = $stmt->execute();
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $leagueStandings[] = $row;
                }
            }
        }
    }
} catch (Exception $e) {
    $errorMessage = "System temporarily unavailable. Please try again later.";
    error_log("PlayerOverview Error: " . $e->getMessage());
    die("
    <!DOCTYPE html>
    <html>
    <head><title>Error</title></head>
    <body><div class='error-container'><h2>Application Error</h2><p>{$errorMessage}</p></div></body>
    </html>");
} finally {
    if ($dbInstance) $dbInstance->closeConnection();
}
?>
<?php

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
    /* Dashboard-Specific Styles */
    .dashboard-page .main-content {
        background: #044D8C;
        color: #ffffff;
        overflow-y: auto;
        padding: 20px;
    }

    /* Metric Cards - For Player Stats */
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

    .dashboard-section h2 {
        margin-top: 0;
        margin-bottom: 15px;
        color: white;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 10px;
    }

    /* Team Roster */
    .roster-table {
        width: 100%;
        border-collapse: collapse;
        color: white;
    }

    .roster-table th {
        background: rgba(255, 255, 255, 0.15);
        padding: 12px 15px;
        text-align: left;
    }

    .roster-table td {
        padding: 10px 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .roster-table tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    /* Matches Section */
    .matches-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .matches-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .match-item {
        background: rgba(255, 255, 255, 0.1);
        padding: 15px;
        border-radius: 8px;
    }

    .match-opponent {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .match-score {
        font-size: 1.2rem;
        margin: 5px 0;
    }

    .match-score.win { color: #4CAF50; }
    .match-score.lose { color: #F44336; }
    .match-score.draw { color: #FFC107; }

    .match-meta {
        display: flex;
        gap: 15px;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }

    /* Standings Table */
    .standings-table {
        width: 100%;
        border-collapse: collapse;
        color: white;
    }

    .standings-table th {
        background: rgba(255, 255, 255, 0.15);
        padding: 12px 15px;
        text-align: left;
    }

    .standings-table td {
        padding: 10px 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .highlight {
        background: rgba(79, 195, 247, 0.2) !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-wrapper {
            flex-direction: column;
        }
        
        .matches-container {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>
<body class="dashboard-page">
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
                <a href="../Homepages/SettingsData.php">Manage Data</a>
                <a href="../Create Account and Login/Login.php">Sign Out</a>
            </div>
        </div>
    </div>
    
   
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
                <a href="../Homepages/SettingsPersonal.php">
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
    
    
    <div class="footer">
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>
    
    
    <div class="main-content">
        <h1>Player Overview</h1>
        
        <!-- Player Stats Cards -->
        <div class="dashboard-metrics">
            <div class="metric-card">
                <div class="metric-value"><?= $player['Appearances'] ?? 0 ?></div>
                <div class="metric-label">Appearances</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= $player['Goals'] ?? 0 ?></div>
                <div class="metric-label">Goals</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= $player['Assists'] ?? 0 ?></div>
                <div class="metric-label">Assists</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= $player['YellowCards'] ?? 0 ?></div>
                <div class="metric-label">Yellow Cards</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= $player['RedCards'] ?? 0 ?></div>
                <div class="metric-label">Red Cards</div>
            </div>
        </div>
        
        <div class="dashboard-wrapper">
            <!-- Team Roster Section -->
            <div class="dashboard-section">
                <h2>TEAM ROSTER</h2>
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
                        <tr <?= ($tp['PlayerID'] == ($player['PlayerID'] ?? null)) ? 'class="highlight"' : '' ?>>
                            <td><?= htmlspecialchars($tp['PlayerName']) ?></td>
                            <td><?= $tp['Goals'] ?></td>
                            <td><?= $tp['Assists'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- League Standings Section -->
            <div class="dashboard-section">
                <h2>LEAGUE STANDINGS</h2>
                <table class="standings-table">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Team</th>
                            <th>Pts</th>
                            <th>GD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leagueStandings as $index => $team): ?>
                        <tr <?= $team['TeamName'] == ($player['TeamName'] ?? '') ? 'class="highlight"' : '' ?>>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($team['TeamName']) ?></td>
                            <td><?= $team['Points'] ?></td>
                            <td><?= ($team['GoalDifference'] > 0 ? '+' : '') . $team['GoalDifference'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Matches Section -->
        <div class="dashboard-section">
            <h2>MATCHES</h2>
            <div class="matches-container">
                <!-- Recent Matches -->
                <div>
                    <h3>RECENT MATCHES</h3>
                    <div class="matches-list">
                        <?php foreach ($recent as $match): 
                            $isHome = $match['HomeTeam'] == ($player['TeamName'] ?? '');
                            $opponent = $isHome ? $match['AwayTeam'] : $match['HomeTeam'];
                            $resultClass = '';
                            if ($isHome && ($match['HomeGoals'] > $match['AwayGoals'])) $resultClass = 'win';
                            elseif (!$isHome && ($match['AwayGoals'] > $match['HomeGoals'])) $resultClass = 'win';
                            elseif ($match['HomeGoals'] == $match['AwayGoals']) $resultClass = 'draw';
                            else $resultClass = 'lose';
                        ?>
                        <div class="match-item">
                            <div class="match-opponent">vs <?= htmlspecialchars($opponent) ?></div>
                            <div class="match-score <?= $resultClass ?>">
                                <?= $isHome ? $match['HomeGoals'] : $match['AwayGoals'] ?> - <?= $isHome ? $match['AwayGoals'] : $match['HomeGoals'] ?>
                            </div>
                            <div class="match-meta">
                                <span><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($match['MatchDate'])) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Upcoming Fixtures -->
                <div class="card" id="fixtures">
    <h2>UPCOMING FIXTURES</h2>
    <?php if (!empty($upcoming)): ?>
        <div class="scrollable-container">
            <?php foreach ($upcoming as $match): ?>
                <div class="match-item">
                    <div class="match-opponent">
                        <?= htmlspecialchars($match['HomeTeam'] . ' vs ' . $match['AwayTeam']) ?>
                    </div>
                    <div class="match-date">
                        <i class="far fa-calendar"></i> 
                        <?= date('M j, Y', strtotime($match['MatchDate'])) ?>
                        <i class="far fa-clock"></i>
                        <?= date('H:i', strtotime($match['MatchDate'])) ?>
                    </div>
                    <?php if (!empty($match['Venue'])): ?>
                        <div class="match-venue">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?= htmlspecialchars($match['Venue']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No upcoming fixtures scheduled</p>
    <?php endif; ?>
</div>
            </div>
        </div>
    </div>
</body>
</html>