<?php
require_once '../Include/db.php'; 

$dbInstance = new Database();
$conn = $dbInstance->getConnection();


$teamId = $teamName = $leagueId = $managerId = $fieldId = '';
$leagues = [];
$managers = [];
$fields = [];
$error = '';

// Fetch data for dropdowns
$leagueQuery = "SELECT LeagueID, Name FROM League";
$managerQuery = "SELECT tm.ManagerID, u.Firstname, u.Surname 
                 FROM TeamManager tm 
                 JOIN User u ON tm.UserID = u.UserID";
$fieldQuery = "SELECT FieldID, Name FROM Field";

$result = $conn->query($leagueQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $leagues[] = $row;
    }
}

$result = $conn->query($managerQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $managers[] = $row;
    }
}

$result = $conn->query($fieldQuery);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $fields[] = $row;
    }
}

// Check if team ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: Teams.php");
    exit();
}

$teamId = $_GET['id'];

// Fetch team data
$query = "SELECT TeamID, TeamName, LeagueID, ManagerID, FieldID 
          FROM Team 
          WHERE TeamID = :teamId";

$stmt = $conn->prepare($query);
$stmt->bindValue(':teamId', $teamId, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if (!$row) {
    header("Location: Teams.php?error=team_not_found");
    exit();
}

// Assign values from database
$teamName = $row['TeamName'];
$leagueId = $row['LeagueID'];
$managerId = $row['ManagerID'];
$fieldId = $row['FieldID'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $teamName = trim($_POST['team_name']);
    $leagueId = trim($_POST['league_id']);
    $managerId = trim($_POST['manager_id']);
    $fieldId = trim($_POST['field_id']);

    // Basic validation
    if (empty($teamName)) {
        $error = "Team name is required!";
    } else {
        // Update the team in the database
        $updateQuery = "UPDATE Team SET 
                        TeamName = :teamName,
                        LeagueID = :leagueId,
                        ManagerID = :managerId,
                        FieldID = :fieldId
                        WHERE TeamID = :teamId";

        $stmt = $conn->prepare($updateQuery);
        $stmt->bindValue(':teamName', $teamName, SQLITE3_TEXT);
        $stmt->bindValue(':leagueId', $leagueId ? $leagueId : null, SQLITE3_INTEGER);
        $stmt->bindValue(':managerId', $managerId ? $managerId : null, SQLITE3_INTEGER);
        $stmt->bindValue(':fieldId', $fieldId ? $fieldId : null, SQLITE3_INTEGER);
        $stmt->bindValue(':teamId', $teamId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            header("Location: Teams.php?updated=1");
            exit();
        } else {
            $error = "Error updating team: " . $conn->lastErrorMsg();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Team</title>
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
        <h2>Edit Team</h2>
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
            <a href="Teams.php">
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
            <h1>Edit Team</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <label for="team_name">Team Name:</label>
                <input type="text" id="team_name" name="team_name" value="<?php echo htmlspecialchars($teamName); ?>" required>
                
                <label for="league_id">League:</label>
                <select id="league_id" name="league_id">
                    <option value="">-- No League --</option>
                    <?php foreach ($leagues as $league): ?>
                        <option value="<?php echo htmlspecialchars($league['LeagueID']); ?>" 
                            <?php echo $league['LeagueID'] == $leagueId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($league['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="manager_id">Manager:</label>
                <select id="manager_id" name="manager_id">
                    <option value="">-- No Manager --</option>
                    <?php foreach ($managers as $manager): ?>
                        <option value="<?php echo htmlspecialchars($manager['ManagerID']); ?>" 
                            <?php echo $manager['ManagerID'] == $managerId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($manager['Firstname'] . ' ' . $manager['Surname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="field_id">Home Field:</label>
                <select id="field_id" name="field_id">
                    <option value="">-- No Field --</option>
                    <?php foreach ($fields as $field): ?>
                        <option value="<?php echo htmlspecialchars($field['FieldID']); ?>" 
                            <?php echo $field['FieldID'] == $fieldId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($field['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div class="form-actions">
                    <a href="Teams.php" class="btn btn-cancel">Cancel</a>
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