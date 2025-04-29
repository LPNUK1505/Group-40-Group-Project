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
    
    // Add SQLite performance optimizations
    $db->exec("PRAGMA journal_mode = WAL");
    $db->exec("PRAGMA synchronous = NORMAL");
    
    session_start();

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        // 1. Get user profile (optimized)
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

            // 2. Get player stats (simplified)
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

                // 3. Get team roster (limited)
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

                // 4. Get recent matches (limited)
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

                // 5. Get upcoming matches (limited)
                $stmt = $db->prepare("
                    SELECT 
                        t1.TeamName AS HomeTeam, 
                        t2.TeamName AS AwayTeam,
                        lm.MatchDate
                    FROM League_Match lm
                    JOIN Team t1 ON lm.HomeTeamID = t1.TeamID
                    JOIN Team t2 ON lm.AwayTeamID = t2.TeamID
                    WHERE (t1.TeamID = :team_id OR t2.TeamID = :team_id)
                    AND lm.Status = 'Scheduled'
                    ORDER BY lm.MatchDate ASC
                    LIMIT 5
                ");
                $stmt->bindValue(':team_id', $user['TeamID'], SQLITE3_INTEGER);
                $result = $stmt->execute();
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $upcoming[] = $row;
                }

                // 6. Get league standings (optimized)
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Overview</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary-color: #03588C;
            --secondary-color: #022340;
            --accent-color: #4fc3f7;
            --text-light: #F2F2F2;
            --text-muted: #b3e5fc;
            --card-bg: linear-gradient(135deg, #1b3d55, #022340);
            --error-border: #e74c3c;
            --error-bg: #fdf7f7;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
        /* Header */
        .header {
            background-color: var(--secondary-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: flex-end;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .profile-box {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            position: relative;
        }
        
        .profile-box i {
            font-size: 1.2rem;
        }
        
        .name {
            font-weight: bold;
        }
        
        .role {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 180px;
            z-index: 1001;
        }
        
        .dropdown a {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .dropdown a:hover {
            background-color: #f0f0f0;
        }
        
        .profile-box:hover .dropdown {
            display: block;
        }
        
        /* Sidebar */
        .sidebar {
            background-color: var(--secondary-color);
            color: white;
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px;
            display: flex;
            flex-direction: column;
        }
        
        .goikon-logo {
            width: 80%;
            margin: 20px auto;
            display: block;
        }
        
        .sidebar-separator {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 10px 20px;
        }
        
        .sidebar-button, .sidebar-toolbox-button {
            padding: 12px 20px;
            transition: background-color 0.2s;
        }
        
        .sidebar-button:hover, .sidebar-toolbox-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-button a, .sidebar-toolbox-button a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-button i, .sidebar-toolbox-button i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-toolbox-container {
            margin-top: auto;
            padding-bottom: 20px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 20px;
            min-height: calc(100vh - 60px);
        }
        
        .main-content h1 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        /* Dashboard Grid */
        .dashboard {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-auto-rows: minmax(150px, auto);
            gap: 20px;
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
            color: var(--text-light);
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
        
        /* Player Stats */
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
        
        /* Standings */
        #standings { 
            grid-column: span 3;
            grid-row: span 2;
        }
        
        /* Matches */
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
            padding-right: 5px;
        }
        
        .scrollable-container::-webkit-scrollbar {
            width: 5px;
        }
        
        .scrollable-container::-webkit-scrollbar-thumb {
            background-color: var(--primary-color);
            border-radius: 5px;
        }
        
        /* Match Items */
        .match-item {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .match-opponent {
            font-weight: bold;
        }
        
        .match-date, .match-venue {
            font-size: 0.9rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }
        
        .match-score {
            font-weight: bold;
            margin: 5px 0;
        }
        
        .match-score.win {
            color: #4CAF50;
        }
        
        .match-score.lose {
            color: #F44336;
        }
        
        .match-score.draw {
            color: #FFC107;
        }
        
        /* Standings Table */
        #standings table {
            width: 100%;
            border-collapse: collapse;
        }
        
        #standings th {
            background-color: var(--primary-color);
            padding: 10px;
            text-align: left;
            position: sticky;
            top: 0;
        }
        
        #standings td {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Highlight Row */
        .highlight {
            background-color: rgba(79, 195, 247, 0.2);
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            background-color: var(--secondary-color);
            color: white;
            padding: 15px 20px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: calc(100% - 250px);
            margin-left: 250px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .footer a {
            color: white;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        
        .footer a:hover {
            opacity: 0.8;
        }
        
        /* Error Page */
        .error-container {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid var(--error-border);
            border-radius: 5px;
            background-color: var(--error-bg);
        }
        
        .error-title {
            color: var(--error-border);
            margin-top: 0;
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
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding-top: 0;
            }
            
            .main-content {
                margin-left: 0;
                margin-top: 0;
            }
            
            .footer {
                width: 100%;
                margin-left: 0;
                position: relative;
            }
            
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
                        <div class="stat-value"><?= $player['Appearances'] ?? 0 ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Goals</div>
                        <div class="stat-value"><?= $player['Goals'] ?? 0 ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assists</div>
                        <div class="stat-value"><?= $player['Assists'] ?? 0 ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Yellow Cards</div>
                        <div class="stat-value"><?= $player['YellowCards'] ?? 0 ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Red Cards</div>
                        <div class="stat-value"><?= $player['RedCards'] ?? 0 ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Team</div>
                        <div class="stat-value"><?= htmlspecialchars($player['TeamName'] ?? 'None') ?></div>
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
                            <tr <?= ($tp['PlayerID'] == ($player['PlayerID'] ?? null)) ? 'class="highlight"' : '' ?>>
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
                            <tr <?= $team['TeamName'] == ($player['TeamName'] ?? '') ? 'class="highlight"' : '' ?>>
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
                    <?php foreach ($upcoming as $match): ?>
                    <div class="match-item">
                        <div class="match-opponent"><?= htmlspecialchars($match['HomeTeam'] . ' vs ' . $match['AwayTeam']) ?></div>
                        <div class="match-date">
                            <i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($match['MatchDate'])) ?>
                            <i class="far fa-clock"></i> <?= date('H:i', strtotime($match['MatchDate'])) ?>
                        </div>
                        <div class="match-venue">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($match['Venue']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>