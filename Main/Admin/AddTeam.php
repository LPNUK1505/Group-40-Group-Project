<?php
include_once __DIR__ . '/../Include/db.php'; // Path to db.php (which includes the Database class)

// Instantiate the Database class to get the connection
try {
    $dbInstance = new Database();  // Instantiate the Database class
    $conn = $dbInstance->getConnection(); // Get the connection
} catch (Exception $e) {
    die("Error: " . $e->getMessage());  // If there's an error, display a message and stop execution
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->exec("BEGIN TRANSACTION");

        // Insert new team
        $stmt = $conn->prepare("INSERT INTO Team (TeamName, LeagueID, FieldID) 
                               VALUES (:team_name, :league_id, :field_id)");
        
        $stmt->bindParam(':team_name', $_POST['team_name']);
        $stmt->bindParam(':league_id', $_POST['league_id']);
        $stmt->bindParam(':field_id', $_POST['field_id']);
        
        $stmt->execute();

        // If manager is selected, update the team with manager
        if (!empty($_POST['manager_id'])) {
            $teamId = $conn->lastInsertRowID();
            
            $stmt = $conn->prepare("UPDATE Team SET ManagerID = :manager_id WHERE TeamID = :team_id");
            $stmt->bindParam(':manager_id', $_POST['manager_id']);
            $stmt->bindParam(':team_id', $teamId);
            $stmt->execute();
        }

        // Commit transaction
        $conn->exec("COMMIT");

        echo "<script>alert('Team added successfully!'); window.location.href='Teams.php';</script>";
    } catch (Exception $e) {
        // If any error occurs, rollback the transaction
        $conn->exec("ROLLBACK");
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Team</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>Add Team</h2>
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
    /* Sidebar is 250px */
    width: calc(100vw - 250px);
    /* Header is 100px and footer is 64px */
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
    max-height: calc(100vh - 70px); /* adjust height below header */
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

.form-container {
    background: #024873;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    width: 350px;
    text-align: center;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.player-content label {
    font-weight: bold;
    display: block;
    margin: 10px 0 5px;
    text-align: left;
    color: white;  /* Label text color */
}

.player-content input, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    background-color: #e9f1f9;  
    color: #333;  
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
</style>

    <!-- Main Content -->
    <div class="player-content">
        <div class="player-form-wrapper">
            <h1>Add New Team</h1>
            <form method="post" action="AddTeam.php">
                <label for="team_name">Team Name:</label>
                <input type="text" id="team_name" name="team_name" required>
                
                <label for="league_id">League:</label>
                <select id="league_id" name="league_id" required>
                    <?php
                    $query = "SELECT LeagueID, Name FROM League";
                    $result = $conn->query($query);
                    if ($result) {
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            echo "<option value='" . $row['LeagueID'] . "'>" . $row['Name'] . "</option>";
                        }
                    }
                    ?>
                </select>
                
                <label for="field_id">Home Field:</label>
                <select id="field_id" name="field_id" required>
                    <?php
                    $query = "SELECT FieldID, Name FROM Field";
                    $result = $conn->query($query);
                    if ($result) {
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            echo "<option value='" . $row['FieldID'] . "'>" . $row['Name'] . "</option>";
                        }
                    }
                    ?>
                </select>
                
                <label for="manager_id">Team Manager (optional):</label>
                <select id="manager_id" name="manager_id">
                    <option value="">-- No Manager --</option>
                    <?php
                    $query = "SELECT tm.ManagerID, u.Firstname, u.Surname 
                              FROM TeamManager tm
                              JOIN User u ON tm.UserID = u.UserID
                              WHERE tm.EndDate IS NULL OR tm.EndDate > date('now')";
                    $result = $conn->query($query);
                    if ($result) {
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            echo "<option value='" . $row['ManagerID'] . "'>" . 
                                 $row['Firstname'] . " " . $row['Surname'] . "</option>";
                        }
                    }
                    ?>
                </select>
                
                <button type="submit">Add Team</button>
            </form>
        </div>
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