<?php include_once __DIR__ . '/../Include/profile_header.php'; ?>
<?php
include_once __DIR__ . '/../Include/db.php';

try {
    $dbInstance = new Database();
    $conn = $dbInstance->getConnection();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// SQL to get player names and stats
$query = "SELECT 
            Team.TeamName,
            Premier_League_Standings.GamesPlayed,
            Premier_League_Standings.GoalDifference,
            Premier_League_Standings.Points
        FROM Premier_League_Standings
        JOIN Team ON Premier_League_Standings.TeamID = Team.TeamID
        ORDER BY Premier_League_Standings.Points DESC, Premier_League_Standings.GoalDifference DESC, Premier_League_Standings.GamesPlayed DESC";

// Debugging: Print the query to ensure it's correct
echo "Running query: <br><pre>$query</pre><br>";

// Execute the query
$result = $conn->query($query);

// Check if query was successful
if ($result) {
    echo "Query executed successfully.<br>";
} else {
    echo "Error executing query: " . $conn->lastErrorMsg() . "<br>";
}

// Initialize an array to hold the standings
$standings = [];

// Check if result is not empty and loop to fetch the data
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    // Debugging: Print each row fetched
    echo "<pre>Fetched Row: ";
    print_r($row);
    echo "</pre>";
    
    $standings[] = $row;
}


// Check if standings array is populated
if (empty($standings)) {
    echo "No standings found.<br>";
} else {
    echo "Standings have been successfully populated.<br>";
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$matchesPerPage = 5;
$offset = ($page - 1) * $matchesPerPage;

$queryMatches = "SELECT 
                    HomeTeam.TeamName AS HomeTeam, 
                    AwayTeam.TeamName AS AwayTeam, 
                    League_Match.HomeGoals || ' - ' || League_Match.AwayGoals AS Result, 
                    League_Match.MatchDate
                FROM 
                    League_Match
                JOIN 
                    Team AS HomeTeam ON League_Match.HomeTeamID = HomeTeam.TeamID
                JOIN 
                    Team AS AwayTeam ON League_Match.AwayTeamID = AwayTeam.TeamID
                WHERE 
                    League_Match.LeagueID = 1 AND
                    League_Match.Status = 'Completed'
                ORDER BY 
                    League_Match.MatchDate DESC
                LIMIT $matchesPerPage OFFSET $offset";

$resultMatches = $conn->query($queryMatches);

$matches = [];
if ($resultMatches->num_rows > 0) {
    // Fetch results into the $matches array
    while ($row = $resultMatches->fetchArray(SQLITE3_ASSOC)) {
        $matches[] = $row;
    }
} else {
    echo "No recent matches found.";
}

// Count total number of matches to calculate total pages
$totalMatchesQuery = "SELECT COUNT(*) as totalMatches FROM League_Match WHERE LeagueID = 1";
$totalMatchesResult = $conn->query($totalMatchesQuery);
$totalMatches = $totalMatchesResult->fetchArray(SQLITE3_ASSOC)['totalMatches'];
$totalPages = ceil($totalMatches / $matchesPerPage);

// Display pagination
echo '<div class="pagination">';
for ($i = 1; $i <= $totalPages; $i++) {
    echo '<a href="?page=' . $i . '" class="' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
}
echo '</div>';

$queryFixtures = "SELECT 
                    HomeTeam.TeamName AS HomeTeam, 
                    AwayTeam.TeamName AS AwayTeam, 
                    League_Match.MatchDate
                FROM 
                    League_Match
                JOIN 
                    Team AS HomeTeam ON League_Match.HomeTeamID = HomeTeam.TeamID
                JOIN 
                    Team AS AwayTeam ON League_Match.AwayTeamID = AwayTeam.TeamID
                WHERE 
                    League_Match.Status = 'Scheduled'
                ORDER BY 
                    League_Match.MatchDate ASC
                LIMIT 5";

$resultFixtures = $conn->query($queryFixtures);
$fixtures = [];
while ($row = $resultFixtures->fetchArray(SQLITE3_ASSOC)) {
    $fixtures[] = $row;
}
?>

<!DOCTYPE html>
 <html>
    <!-- JAVA line for 'fontawesome' icons -->
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <!-- Creates the Header at the top -->
    <?php include_once __DIR__ . '/../Include/profile_header.php'; ?>
    
    <!-- Creates the Sidebar on the left hand side -->
    <div class="sidebar">
        <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">
        <!-- Creates buttons in the Sidebar -->
        <div class="sidebar-separator"></div>
        <div class="sidebar-button">
            <a href="TeamOverview.php" class="stayOnPageLink">
                <i class="fa-solid fa-people-group"></i>Team Overview
            </a>
        </div>
        <div class="sidebar-button">
            <a href="RosterManagement.php">
                <i class="fa-solid fa-user-plus"></i>Roster Management
            </a>
        </div>
        <div class="sidebar-button">
            <a href="PlayerStats.php">
                <i class="fa-solid fa-chart-simple"></i>Player Stats
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
                    <i class="fa-solid fa-right-from-bracket"></i></i>Sign Out
                </a>
            </div>
        </div>
    </div>
    <!-- Creates the Footer at the bottom -->
    <div class="footer">
        <!-- Creates buttons in the footer -->
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>

    <!-- Creates Main Content area -->
    <div class="main-content">
        <style>
            .dashboard {
                display: grid;
                grid-template-columns: 3fr 3fr 3fr 3fr;
                grid-template-rows: auto auto;
                height: auto;
                gap: 15px;
                padding: 5px 15px 15px 15px;
                box-sizing: border-box;
                overflow: hidden;
            }

            #standings { 
                grid-column: 1 / 2;
                grid-row: 1 / 3;
            }

            #friendly {
                grid-column: 2 / 3;
                grid-row: 1;
            }

            #recent-matches {
                grid-column: 3 / 4;
                grid-row: 1;
            }

            #squad-summary {
                grid-column: 4 / 5;
                grid-row: 1;
            }

            #fixtures {
                grid-column: 2 / 5;
                grid-row: 2;
            }

            .card {
                display: flex;
                flex-direction: column;
                background: linear-gradient(135deg, #1b3d55, #022340);
                padding: 0px 20px;
                border-radius: 15px;
                box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.2);
                color: #FFFFFF;
                box-sizing: border-box;
                overflow: hidden;
                border: 3px solid #044D8C;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                min-width: 0;
                min-height: 0;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 4px 8px 25px rgba(0, 0, 0, 0.3);
            }

            ul {
                list-style: none;
                padding: 0;
            }

            ul li {
                padding: 5px 0;
            }

            .scrollable-container {
                overflow-y: auto;
                max-height: 100%;
            }

            .scrollable-container::-webkit-scrollbar {
                width: 0;
                height: 0;
            }

            .squad-summary-table {
                position: sticky;
                width: 100%;
                margin: 20px;
                border-collapse: collapse;
                background-color: rgba(255, 255, 255, 0.1);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 10px;
                overflow: hidden;
                width: 100%;
                table-layout: fixed;

            }

            .squad-summary-table th {
                background-color: #0E304A;
                color: #F2F2F2;
                font-weight: bold;
                text-transform: uppercase;
            }

            .squad-summary-table tr:nth-child(even) {
                background-color: #1b3d55;
            }

            .squad-summary-table tr:hover {
                background-color: rgba(255, 255, 255, 0.1);
                transition: 0.3s ease-in-out;
            }

            table {
                position: sticky;
                width: 100%;
                margin: 20px;
                border-collapse: collapse;
                background-color: #024873;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 10px;
                overflow: hidden;
            }

            table th, table td {
                padding: 12px;
                border: 1px solid white;
                text-align: center;
            }

            table th {
                background-color: #03588C;
                color: #F2F2F2;
                font-weight: bold;
                text-transform: uppercase;
            }

            table tr:nth-child(even) {
                background-color: #0271A1;
            }

            table tr:hover {
                background-color: #046b9d;
                transition: 0.3s ease-in-out;
            }

            #friendly button {
                border: none;
                cursor: pointer;
                font-size: 18px;
                padding: 5px;
                border-radius: 5px;
                width: 32px;
                height: 32px;
                text-align: center;
                line-height: 1;
            }

            .team-list {
                list-style: none;
                padding-left: 0;
                margin-top: 10px;
            }

            .team-list-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .team-list-header a{
                display: flex;
                align-items: center;
                width: min-content;
                font-size: 15px;
                gap: 5px;
                color: white;
                font-weight: bold;
                border: none;
                border-radius: 20px;
                padding: 5px 10px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .team-list-header a:hover {
                background-color: #024873;
            }

            .team-list-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px;
                margin: 10px 0;
                background-color: rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                transition: background-color 0.3s ease, transform 0.3s ease;
            }

            .booking-action-button {
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: bold;
                cursor: pointer;
                border: none;
                transition: background-color 0.3s ease;
            }

            .booking-action-button.accept {
                background-color: #28a745;
                color: white;
            }

            .booking-action-button.accept:hover {
                background-color: #218838;
            }

            .booking-action-button.deny {
                background-color: #dc3545;
                color: white;
            }

            .booking-action-button.deny:hover {
                background-color: #c82333;
            }

            .dashboard h2{
                position: sticky;
                width: 100%;
                top: 0;
                text-align: left;
                color: #f2f2f2;
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 10px;
                letter-spacing: 1px;
                text-transform: uppercase;
                z-index: 1;
            }

            .team-list-item-text-container {
                display: grid;
                flex-direction: row;
                width: 100%;
            }

            .match-date-time {
                font-size: 14px;
                color: #E1E1E1;
                margin-left: 15px;
            }

            .match-info {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-right: 10px;
            }

            .match-score.win {
                font-size: 18px;
                font-weight: bold;
                color: #28a745;
            }

            .match-score.draw {
                font-size: 18px;
                font-weight: bold;
                color: #ffc107;
            }

            .match-score.lose {
                font-size: 18px;
                font-weight: bold;
                color: #dc3545;
            }

            .fixture-list {
                width: 100%;
                list-style: none;
                margin-top: 0;
            }

            .fixture-list-item {
                width: 100%;
                margin: 12px 0;
                border-radius: 10px;
                background-color: #1b3d55;
            }

            .fixture-list-item-text-container {
                display: flex;
                flex-direction: column;
                width: 100%;
            }

            .fixture-list a{
                width: calc(100% - 24px);
                background-color: inherit;
            }

            .fixture-list-item-additional{
                gap: 15px;
                margin-left: 15px;
            }

            .fixture-list-item:hover {
                background: linear-gradient(135deg, #224766, #12314a);
                transform: scale(1.02);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            }

            .pagination {
            display: flex;
            justify-content: center;
            margin-top: 10px;
            gap: 8px;
            }

            .pagination a {
                color: white;
                background-color: #044D8C;
                padding: 6px 12px;
                border-radius: 5px;
                text-decoration: none;
                transition: background-color 0.3s ease;
            }

            .pagination a.active,
            .pagination a:hover {
                background-color: #0271A1;
                font-weight: bold;
            }

        </style>

        <h1> Team Overview </h1>
        <div class="dashboard">
            <div class="card"id="fixtures">
            <div class="team-list-header">
                <h2>Upcoming Fixtures</h2>
            </div>

                <div class="scrollable-container">
                    <ul class="fixture-list">
                    <?php if (!empty($fixtures)): ?>
                        <?php foreach ($fixtures as $row): ?>
                            <li class="fixture-list-item">
                                <div class="fixture-list-item-text-container">
                                    <span><?= htmlspecialchars($row['HomeTeam']) ?> vs <?= htmlspecialchars($row['AwayTeam']) ?></span>
                                    <span class="match-date-time"><?= date("F j, Y - H:i", strtotime($row['MatchDate'])) ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="fixture-list-item">No upcoming fixtures scheduled.</li>
                    <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="card" id="standings">
                <div class="team-list-header">
                    <h2>League Standings</h2>
                </div>

                <div class="scrollable-container">
                    <table>
                        <thead>
                            <th>POS</th>
                            <th>TEAM</th>
                            <th>PL</th>
                            <th>GD</th>
                            <th>PTS</th>
                        </thead>
                        <tbody>
                            <?php
                            if (count($standings) > 0) {
                                $position = 1;
                                foreach ($standings as $row) {
                                    echo "<tr>";
                                    echo "<td>" . $position++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['TeamName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['GamesPlayed']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['GoalDifference']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Points']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No standings found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" id="friendly">
                <div class="team-list-header">
                    <h2>Friendly Bookings</h2>
                    <a href="#">
                        <i class="fa-solid fa-plus"></i>Create
                    </a>
                </div>

                <div class="scrollable-container">
                    <ul class="team-list">
                        <li class="team-list-item">
                            <div class="team-list-item-text-container">
                                <span>Charlie City vs Hotel FC</span>
                                <span class="match-date-time">April 9, 2025 - 17:00</span>
                            </div>

                            <div>
                                <div>
                                    <button class="booking-action-button accept"><i class="fa-solid fa-check"></i></button>
                                    <button class="booking-action-button deny"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                        </li>

                        <li class="team-list-item">
                            <div class="team-list-item-text-container">
                                <span>Charlie City vs Alpha FC</span>
                                <span class="match-date-time">April 27, 2025 - 15:30</span>
                            </div>
                            <div>

                                <button class="booking-action-button accept"><i class="fa-solid fa-check"></i></button>
                                <button class="booking-action-button deny"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </li>

                        <li class="team-list-item">
                            <div class="team-list-item-text-container">
                                <span>Charlie City vs Oscar Knights</span>
                                <span class="match-date-time">March 30, 2025 - 15:00</span>
                            </div>

                            <div>
                                <button class="booking-action-button accept"><i class="fa-solid fa-check"></i></button>
                                <button class="booking-action-button deny"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card" id="recent-matches">
                <div class="team-list-header">
                    <h2>Recent Matches</h2>
                </div>
                    <div class="scrollable-container">
                        <ul class="team-list">
                            <?php
                                $row = $resultMatches->fetchArray(SQLITE3_ASSOC); // Fetch the first row to check if data exists
                                if ($row) {
                                    do {
                                        // Extract match details
                                        $homeTeam = htmlspecialchars($row['HomeTeam']);
                                        $awayTeam = htmlspecialchars($row['AwayTeam']);
                                        $result = htmlspecialchars($row['Result']);
                                        $matchDate = date("F j, Y", strtotime($row['MatchDate'])); // Formatting date
                    
                                        // Determine match outcome
                                        $matchScoreClass = "";
                                        if (strpos($result, "win") !== false) {
                                            $matchScoreClass = "win";
                                        } elseif (strpos($result, "draw") !== false) {
                                            $matchScoreClass = "draw";
                                        } else {
                                            $matchScoreClass = "lose";
                                        }
                    
                                        // Display match
                                        echo "<li class='team-list-item'>
                                                <div class='team-list-item-text-container'>
                                                    <div class='match-info'>
                                                        <span class='match-details'>vs $awayTeam</span>
                                                        <span class='match-score $matchScoreClass'>$result</span>
                                                    </div>
                                                    <span class='match-date-time'>$matchDate</span>
                                                </div>
                                              </li>";
                                    } while ($row = $resultMatches->fetchArray(SQLITE3_ASSOC)); // Continue fetching next row
                                } else {
                                    echo "<li class='team-list-item'><div class='team-list-item-text-container'><span>No recent matches found.</span></div></li>";
                                }
                            ?>
                        </ul>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
            </div>

            <div class="card" id="squad-summary">
                <div class="team-list-header">
                    <h2>Squad Summary</h2>
                </div>

                <div class="scrollable-container">
                            <table class="squad-summary-table">
                                <tr><th>#</th><th>Player</th><th>Event</th><th>Pos</th></tr>
                                <tr><td>3</td><td>Tyrone Mings</td><td>Red Card</td><td>CB</td></tr>
                                <tr><td>4</td><td>John Terry</td><td>Injured</td><td>CB</td></tr>
                                <tr><td>2</td><td>Gary Cahill</td><td>Yellow Card</td><td>CB</td></tr>
                            </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</html>