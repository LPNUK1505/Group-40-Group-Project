<?php
include_once __DIR__ . '/../Include/db.php'; 

try {
    $dbInstance = new Database();
    $conn = $dbInstance->getConnection();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Fetch data for dropdowns
$teams = [];
$leagues = [];
$fields = [];
$referees = [];

$teamQuery = "SELECT TeamID, TeamName FROM Team";
$leagueQuery = "SELECT LeagueID, Name FROM League";
$fieldQuery = "SELECT FieldID, Name FROM Field";
$refereeQuery = "SELECT r.RefereeID, u.Firstname, u.Surname 
                FROM Referee r JOIN User u ON r.UserID = u.UserID";

$result = $conn->query($teamQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $teams[] = $row;
    }
}

$result = $conn->query($leagueQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $leagues[] = $row;
    }
}

$result = $conn->query($fieldQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $fields[] = $row;
    }
}

$result = $conn->query($refereeQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $referees[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->exec("BEGIN TRANSACTION");

        $matchType = $_POST['match_type'];
        $status = 'Scheduled'; // Default status
        
        if ($matchType === 'league') {
            $stmt = $conn->prepare("INSERT INTO League_Match (
                LeagueID, HomeTeamID, AwayTeamID, MatchDate, Venue, Status
            ) VALUES (
                :league_id, :home_team, :away_team, :match_date, :venue, :status
            )");
            
            $stmt->bindParam(':league_id', $_POST['league_id']);
            $stmt->bindParam(':home_team', $_POST['home_team']);
            $stmt->bindParam(':away_team', $_POST['away_team']);
            $stmt->bindParam(':match_date', $_POST['match_date']);
            $stmt->bindParam(':venue', $_POST['venue']);
            $stmt->bindParam(':status', $status);
            
            $stmt->execute();
            
            $matchId = $conn->lastInsertRowID();
            $matchTable = 'League_Match';
        } else {
            $stmt = $conn->prepare("INSERT INTO Friendly_Match (
                FieldID, TeamID, OpposingTeamID, MatchDate, Status
            ) VALUES (
                :field_id, :team_id, :opposing_team, :match_date, :status
            )");
            
            $stmt->bindParam(':field_id', $_POST['field_id']);
            $stmt->bindParam(':team_id', $_POST['team_id']);
            $stmt->bindParam(':opposing_team', $_POST['opposing_team']);
            $stmt->bindParam(':match_date', $_POST['match_date']);
            $stmt->bindParam(':status', $status);
            
            $stmt->execute();
            
            $matchId = $conn->lastInsertRowID();
            $matchTable = 'Friendly_Match';
        }
        
        // Assign referee if selected
        if (!empty($_POST['referee_id'])) {
            $stmt = $conn->prepare("INSERT INTO Referee_Booking (
                RefereeID, " . ($matchType === 'league' ? 'LeagueMatchID' : 'FriendlyMatchID') . ", Status
            ) VALUES (
                :referee_id, :match_id, :status
            )");
            
            $stmt->bindParam(':referee_id', $_POST['referee_id']);
            $stmt->bindParam(':match_id', $matchId);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
        }
        
        $conn->exec("COMMIT");
        echo "<script>alert('Match added successfully!'); window.location.href='Matches.php';</script>";
    } catch (Exception $e) {
        $conn->exec("ROLLBACK");
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Match</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>Add Match</h2>
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
            <a href="Matches.php">
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
    
    <style>
    .player-content {
        position: fixed;
        align-content: left;
        top: 100px;
        left: 250px;
        width: calc(100vw - 250px);
        height: calc(100vh - 164px);
        background-color: #022340;
        z-index: 1000001;
        font-family:Verdana, Tahoma, sans-serif;
    }

    .player-content button {
        background-color: #03588C;
        color: #F2F2F2;
        text-align: center;
        font-weight: bold;
        text-decoration: none;
        font-size: 18px;
        width: 230px;
        display: flex;
        padding: 10px 0;
        display: block;
        border: 3px solid white;
        border-radius: 50px;
        margin: 0 auto;
    }
    
    .player-content::-webkit-scrollbar {
        width: 8px;
    }
    
    .player-content::-webkit-scrollbar-thumb {
        background-color: #1e4e75;
        border-radius: 10px;
    }

    .player-content {
        flex-grow: 1;
        padding: 20px;
        overflow-y: auto;
        max-height: calc(100vh - 70px);
        box-sizing: border-box;
    }

    .player-content h1{
        text-align: center;
        font-size: 40px;
        color: #F2F2F2;
        cursor: pointer;
    }

    .player-content p{
        text-align: center;
        font-size: 20px;
        color: #f2f2f2;
        font:600;
        line-height: normal;
    }

    .player-content h3{
        text-align: center;
        font-size: 28px;
        color: #f2f2f2;
        font:600;
        height: 100px;
    }

    .player-content ol{
        text-align: center;
        font-size: 20px;
        color: #f2f2f2;
        font:600;
    }

    .player-content table, th, td{
        border: 1px solid white;
        border-collapse: collapse;
        color: #F2F2F2;
        text-align: center;
        margin-left: auto;
        margin-right: auto;
    }

    .player-content a{
        background-color: #03588C;
        color: #F2F2F2;
        text-align: center;
        font-size: 18px;
        width: 250px;
        display: flex;
        padding: 10px;
        display: block;
        margin: auto;
        text-decoration: none;
    }
    
    .player-form-wrapper {
        background: linear-gradient(145deg, #002b44, #004c70);
        max-width: 650px;
        margin: 40px auto;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        color: #f1f1f1;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .player-form-wrapper h1 {
        text-align: center;
        font-size: 28px;
        color: #ffffff;
        margin-bottom: 30px;
        text-shadow: 1px 1px 3px #000;
    }

    .player-form-wrapper form {
        display: flex;
        flex-direction: column;
    }

    .player-form-wrapper label {
        font-size: 15px;
        margin-bottom: 5px;
        margin-top: 15px;
        color: #d2e9ff;
        font-weight: 600;
    }

    .player-form-wrapper input,
    .player-form-wrapper select {
        padding: 12px;
        border-radius: 8px;
        border: none;
        font-size: 15px;
        background-color: #e9f3fb;
        margin-bottom: 10px;
        transition: border 0.2s ease;
    }

    .player-form-wrapper input:focus,
    .player-form-wrapper select:focus {
        border: 2px solid #00c4ff;
        outline: none;
    }

    .player-form-wrapper button[type="submit"] {
        margin-top: 20px;
        padding: 14px;
        background: linear-gradient(90deg, #00c4ff, #0059a8);
        border: none;
        color: white;
        font-size: 16px;
        font-weight: bold;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .player-form-wrapper button[type="submit"]:hover {
        background: linear-gradient(90deg, #009ecf, #004b91);
    }
    
    .match-type-toggle {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    .match-type-toggle button {
        padding: 10px 20px;
        margin: 0 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .match-type-toggle button.active {
        background: linear-gradient(90deg, #00c4ff, #0059a8);
        color: white;
    }
    
    .match-type-toggle button.inactive {
        background: #e9f3fb;
        color: #333;
    }
    
    .match-form-section {
        display: none;
    }
    
    .match-form-section.active {
        display: block;
    }
</style>

    <!-- Main Content -->
    <div class="player-content">
        <div class="player-form-wrapper">
            <h1>Add New Match</h1>
            
            <div class="match-type-toggle">
                <button type="button" id="league-match-btn" class="active">League Match</button>
                <button type="button" id="friendly-match-btn" class="inactive">Friendly Match</button>
            </div>
            
            <form method="post" action="AddMatch.php">
                <input type="hidden" name="match_type" id="match_type" value="league">
                
                <!-- League Match Form -->
                <div id="league-match-form" class="match-form-section active">
                    <label for="league_id">League:</label>
                    <select id="league_id" name="league_id" required>
                        <?php foreach ($leagues as $league): ?>
                            <option value="<?php echo htmlspecialchars($league['LeagueID']); ?>">
                                <?php echo htmlspecialchars($league['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="home_team">Home Team:</label>
                    <select id="home_team" name="home_team" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>">
                                <?php echo htmlspecialchars($team['TeamName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="away_team">Away Team:</label>
                    <select id="away_team" name="away_team" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>">
                                <?php echo htmlspecialchars($team['TeamName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="venue">Venue:</label>
                    <input type="text" id="venue" name="venue" required>
                </div>
                
                <!-- Friendly Match Form -->
                <div id="friendly-match-form" class="match-form-section">
                    <label for="field_id">Field:</label>
                    <select id="field_id" name="field_id">
                        <?php foreach ($fields as $field): ?>
                            <option value="<?php echo htmlspecialchars($field['FieldID']); ?>">
                                <?php echo htmlspecialchars($field['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="team_id">Team:</label>
                    <select id="team_id" name="team_id">
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>">
                                <?php echo htmlspecialchars($team['TeamName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="opposing_team">Opposing Team:</label>
                    <select id="opposing_team" name="opposing_team">
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>">
                                <?php echo htmlspecialchars($team['TeamName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Common Fields -->
                <label for="match_date">Match Date & Time:</label>
                <input type="datetime-local" id="match_date" name="match_date" required>
                
                <label for="referee_id">Referee (optional):</label>
                <select id="referee_id" name="referee_id">
                    <option value="">-- No Referee --</option>
                    <?php foreach ($referees as $referee): ?>
                        <option value="<?php echo htmlspecialchars($referee['RefereeID']); ?>">
                            <?php echo htmlspecialchars($referee['Firstname'] . ' ' . $referee['Surname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit">Add Match</button>
            </form>
        </div>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>

    <script src="../sidebar.js"></script>
    <script>
        window.onload = function() {
            preventPageRefresh();
            
            // Toggle between league and friendly match forms
            const leagueBtn = document.getElementById('league-match-btn');
            const friendlyBtn = document.getElementById('friendly-match-btn');
            const leagueForm = document.getElementById('league-match-form');
            const friendlyForm = document.getElementById('friendly-match-form');
            const matchType = document.getElementById('match_type');
            
            leagueBtn.addEventListener('click', function() {
                leagueBtn.className = 'active';
                friendlyBtn.className = 'inactive';
                leagueForm.className = 'match-form-section active';
                friendlyForm.className = 'match-form-section';
                matchType.value = 'league';
            });
            
            friendlyBtn.addEventListener('click', function() {
                leagueBtn.className = 'inactive';
                friendlyBtn.className = 'active';
                leagueForm.className = 'match-form-section';
                friendlyForm.className = 'match-form-section active';
                matchType.value = 'friendly';
            });
        };
        
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