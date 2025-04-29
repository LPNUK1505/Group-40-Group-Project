<?php
require_once '../Include/db.php'; // Include database connection

$dbInstance = new Database();
$conn = $dbInstance->getConnection();

$matchId = $matchType = '';
$leagueId = $homeTeamId = $awayTeamId = $fieldId = $teamId = $opposingTeamId = '';
$matchDate = $status = '';
$leagues = [];
$teams = [];
$fields = [];
$referees = [];
$error = '';

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    header("Location: Matches.php");
    exit();
}

$matchId = $_GET['id'];
$matchType = $_GET['type'];

// Fetch data for dropdowns
$leagueQuery = "SELECT LeagueID, Name FROM League";
$teamQuery = "SELECT TeamID, TeamName FROM Team";
$fieldQuery = "SELECT FieldID, Name FROM Field";
$refereeQuery = "SELECT r.RefereeID, u.Firstname, u.Surname 
                FROM Referee r JOIN User u ON r.UserID = u.UserID";

$result = $conn->query($leagueQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $leagues[] = $row;
    }
}

$result = $conn->query($teamQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $teams[] = $row;
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

// Fetch match data based on type
if ($matchType === 'league') {
    $query = "SELECT 
                lm.LeagueMatchID AS MatchID,
                lm.LeagueID,
                lm.HomeTeamID,
                lm.AwayTeamID,
                lm.MatchDate,
                lm.FieldID,
                lm.Status,
                rb.RefereeID
              FROM League_Match lm
              LEFT JOIN Referee_Booking rb ON lm.LeagueMatchID = rb.LeagueMatchID
              WHERE lm.LeagueMatchID = :matchId";
} else {
    $query = "SELECT 
                fm.FriendlyMatchID AS MatchID,
                fm.FieldID,
                fm.TeamID,
                fm.OpposingTeamID,
                fm.MatchDate,
                fm.Status,
                rb.RefereeID
              FROM Friendly_Match fm
              LEFT JOIN Referee_Booking rb ON fm.FriendlyMatchID = rb.FriendlyMatchID
              WHERE fm.FriendlyMatchID = :matchId";
}

$stmt = $conn->prepare($query);
$stmt->bindValue(':matchId', $matchId, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if (!$row) {
    header("Location: Matches.php?error=match_not_found");
    exit();
}

// Assign values from database
if ($matchType === 'league') {
    $leagueId = $row['LeagueID'];
    $homeTeamId = $row['HomeTeamID'];
    $awayTeamId = $row['AwayTeamID'];
    $matchDate = $row['MatchDate'];
    $fieldId = $row['FieldID'];
    $status = $row['Status'];
    $refereeId = $row['RefereeID'];
} else {
    $fieldId = $row['FieldID'];
    $teamId = $row['TeamID'];
    $opposingTeamId = $row['OpposingTeamID'];
    $matchDate = $row['MatchDate'];
    $status = $row['Status'];
    $refereeId = $row['RefereeID'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $matchDate = trim($_POST['match_date']);
    $status = trim($_POST['status']);
    $refereeId = trim($_POST['referee_id']);

    if ($matchType === 'league') {
        $leagueId = trim($_POST['league_id']);
        $homeTeamId = trim($_POST['home_team']);
        $awayTeamId = trim($_POST['away_team']);
        $fieldId = trim($_POST['field_id']);
    } else {
        $fieldId = trim($_POST['field_id']);
        $teamId = trim($_POST['team_id']);
        $opposingTeamId = trim($_POST['opposing_team']);
    }

    if (empty($matchDate) || empty($status)) {
        $error = "Required fields are missing!";
    } elseif ($matchType === 'league' && $homeTeamId === $awayTeamId) {
        $error = "Home team and away team cannot be the same!";
    } elseif ($matchType === 'friendly' && $teamId === $opposingTeamId) {
        $error = "Team and opposing team cannot be the same!";
    } else {
        try {
            $conn->exec("BEGIN TRANSACTION");

            // Update the match in the database
            if ($matchType === 'league') {
                $updateQuery = "UPDATE League_Match SET 
                                LeagueID = :leagueId,
                                HomeTeamID = :homeTeamId,
                                AwayTeamID = :awayTeamId,
                                FieldID = :fieldId,
                                MatchDate = :matchDate,
                                Status = :status
                                WHERE LeagueMatchID = :matchId";
                
                $stmt = $conn->prepare($updateQuery);
                $stmt->bindValue(':leagueId', $leagueId, SQLITE3_INTEGER);
                $stmt->bindValue(':homeTeamId', $homeTeamId, SQLITE3_INTEGER);
                $stmt->bindValue(':awayTeamId', $awayTeamId, SQLITE3_INTEGER);
                $stmt->bindValue(':fieldId', $fieldId, SQLITE3_INTEGER);
                $stmt->bindValue(':matchDate', $matchDate, SQLITE3_TEXT);
                $stmt->bindValue(':status', $status, SQLITE3_TEXT);
                $stmt->bindValue(':matchId', $matchId, SQLITE3_INTEGER);
            } else {
                $updateQuery = "UPDATE Friendly_Match SET 
                                FieldID = :fieldId,
                                TeamID = :teamId,
                                OpposingTeamID = :opposingTeamId,
                                MatchDate = :matchDate,
                                Status = :status
                                WHERE FriendlyMatchID = :matchId";
                
                $stmt = $conn->prepare($updateQuery);
                $stmt->bindValue(':fieldId', $fieldId, SQLITE3_INTEGER);
                $stmt->bindValue(':teamId', $teamId, SQLITE3_INTEGER);
                $stmt->bindValue(':opposingTeamId', $opposingTeamId, SQLITE3_INTEGER);
                $stmt->bindValue(':matchDate', $matchDate, SQLITE3_TEXT);
                $stmt->bindValue(':status', $status, SQLITE3_TEXT);
                $stmt->bindValue(':matchId', $matchId, SQLITE3_INTEGER);
            }

            $stmt->execute();

            // Handle referee assignment
            $refereeColumn = ($matchType === 'league') ? 'LeagueMatchID' : 'FriendlyMatchID';
            
            // First delete any existing referee booking
            $deleteRefereeQuery = "DELETE FROM Referee_Booking WHERE $refereeColumn = :matchId";
            $stmt = $conn->prepare($deleteRefereeQuery);
            $stmt->bindValue(':matchId', $matchId, SQLITE3_INTEGER);
            $stmt->execute();

            // Add new referee booking if selected
            if (!empty($refereeId)) {
                $insertRefereeQuery = "INSERT INTO Referee_Booking (
                    RefereeID, $refereeColumn, Status
                ) VALUES (
                    :refereeId, :matchId, :status
                )";
                
                $stmt = $conn->prepare($insertRefereeQuery);
                $stmt->bindValue(':refereeId', $refereeId, SQLITE3_INTEGER);
                $stmt->bindValue(':matchId', $matchId, SQLITE3_INTEGER);
                $stmt->bindValue(':status', $status, SQLITE3_TEXT);
                $stmt->execute();
            }

            $conn->exec("COMMIT");
            header("Location: Matches.php?updated=1");
            exit();
        } catch (Exception $e) {
            $conn->exec("ROLLBACK");
            $error = "Error updating match: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Match</title>
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
        }

        .user-form-wrapper {
            background: linear-gradient(145deg, #002b44, #004c70);
            max-width: 650px;
            margin: 40px auto;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: #f1f1f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .user-form-wrapper h1 {
            text-align: center;
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 30px;
            text-shadow: 1px 1px 3px #000;
        }

        .user-form-wrapper form {
            display: flex;
            flex-direction: column;
        }

        .user-form-wrapper label {
            font-size: 15px;
            margin-bottom: 5px;
            margin-top: 15px;
            color: #d2e9ff;
            font-weight: 600;
        }

        .user-form-wrapper input,
        .user-form-wrapper select {
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            background-color: #e9f3fb;
            margin-bottom: 10px;
            transition: border 0.2s ease;
        }

        .user-form-wrapper input:focus,
        .user-form-wrapper select:focus {
            border: 2px solid #00c4ff;
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-save {
            background: linear-gradient(90deg, #00c4ff, #0059a8);
            color: white;
        }

        .btn-save:hover {
            background: linear-gradient(90deg, #009ecf, #004b91);
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }

        .error-message {
            color: #ff6b6b;
            background-color: rgba(255, 107, 107, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ff6b6b;
            font-size: 14px;
        }

        .match-type-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: capitalize;
        }

        .league-badge {
            background-color: #3498db;
            color: white;
        }

        .friendly-badge {
            background-color: #2ecc71;
            color: white;
        }

        @media (max-width: 768px) {
            .user-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .user-form-wrapper {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>Edit Match</h2>
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
                <i class="fa-solid fa-arrow-left"></i> Go Back
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
    
    <!-- Main Content -->
    <div class="user-content">
        <div class="user-form-wrapper">
            <h1>Edit Match <span class="match-type-badge <?php echo $matchType === 'league' ? 'league-badge' : 'friendly-badge'; ?>">
                <?php echo htmlspecialchars(ucfirst($matchType)); ?>
            </span></h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <?php if ($matchType === 'league'): ?>
                    <label for="league_id">League:</label>
                    <select id="league_id" name="league_id" required>
                        <?php foreach ($leagues as $league): ?>
                            <option value="<?php echo htmlspecialchars($league['LeagueID']); ?>"
                                <?php echo $league['LeagueID'] == $leagueId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($league['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="home_team">Home Team:</label>
                    <select id="home_team" name="home_team" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>"
                                <?php echo $team['TeamID'] == $homeTeamId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($team['TeamName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="away_team">Away Team:</label>
                    <select id="away_team" name="away_team" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>"
                                <?php echo $team['TeamID'] == $awayTeamId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($team['TeamName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="field_id">Field:</label>
                    <select id="field_id" name="field_id" required>
                        <?php foreach ($fields as $field): ?>
                            <option value="<?php echo htmlspecialchars($field['FieldID']); ?>"
                                <?php echo $field['FieldID'] == $fieldId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($field['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <label for="field_id">Field:</label>
                    <select id="field_id" name="field_id" required>
                        <?php foreach ($fields as $field): ?>
                            <option value="<?php echo htmlspecialchars($field['FieldID']); ?>"
                                <?php echo $field['FieldID'] == $fieldId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($field['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="team_id">Team:</label>
                    <select id="team_id" name="team_id" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>"
                                <?php echo $team['TeamID'] == $teamId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($team['TeamName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="opposing_team">Opposing Team:</label>
                    <select id="opposing_team" name="opposing_team" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>"
                                <?php echo $team['TeamID'] == $opposingTeamId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($team['TeamName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                
                <!-- Common Fields -->
                <label for="match_date">Match Date & Time:</label>
                <input type="datetime-local" id="match_date" name="match_date" 
                       value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($matchDate))); ?>" required>
                
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Scheduled" <?php echo $status === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="Ongoing" <?php echo $status === 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo $status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                
                <label for="referee_id">Referee (optional):</label>
                <select id="referee_id" name="referee_id">
                    <option value="">-- No Referee --</option>
                    <?php foreach ($referees as $referee): ?>
                        <option value="<?php echo htmlspecialchars($referee['RefereeID']); ?>"
                            <?php echo $referee['RefereeID'] == $refereeId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($referee['Firstname'] . ' ' . $referee['Surname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div class="form-actions">
                    <a href="Matches.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save">Save Changes</button>
                </div>
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