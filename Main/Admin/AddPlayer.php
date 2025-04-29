<?php
session_start();

// Check if user is logged in - ADDED MISSING SESSION CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Create Account and Login/Login.php");
    exit();
}

require_once '../Include/db.php';

// Get user info - ADDED USER INFO FETCHING
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'Admin';

// Database connection - CHANGED TO USE $conn CONSISTENTLY
$dbInstance = new Database();
$conn = $dbInstance->getConnection();

// Get user details - ADDED USER DETAILS QUERY
$userQuery = $conn->prepare("SELECT Firstname, Surname FROM User WHERE UserID = ?");
$userQuery->bindValue(1, $user_id, SQLITE3_INTEGER);
$userResult = $userQuery->execute();
$userData = $userResult->fetchArray();

$userDisplay = [
    'name' => $userData ? htmlspecialchars($userData['Firstname'] . ' ' . $userData['Surname']) : 'Admin User',
    'role' => htmlspecialchars($user_role)
];
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->exec("BEGIN TRANSACTION");

        // Step 1: Insert new player into player table (including TeamID)
        $stmt = $conn->prepare("INSERT INTO user (Firstname, Surname, Password, Username, DateOfBirth, Nationality, PhoneNumber, Email, AccountType, RoleID)
                                VALUES (:firstname, :surname, :password, :username, :date_of_birth, :nationality, :phone_number, :email, 'Player', 2)");

        $stmt->bindParam(':firstname', $_POST['firstname']);
        $stmt->bindParam(':surname', $_POST['surname']);
        $stmt->bindParam(':password', $_POST['password']);
        $stmt->bindParam(':username', $_POST['username']);
        $stmt->bindParam(':date_of_birth', $_POST['date_of_birth']);
        $stmt->bindParam(':nationality', $_POST['nationality']);
        $stmt->bindParam(':phone_number', $_POST['phone_number']);
        $stmt->bindParam(':email', $_POST['email']);

        $stmt->execute();

        // Get the last inserted UserID
        $userId = $conn->lastInsertRowID();

        // Step 2: Insert into player table with TeamID
        $stmt = $conn->prepare("INSERT INTO player (UserID, TeamID) VALUES (:user_id, :team_id)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':team_id', $_POST['team_id']);  // Use the TeamID from the form
        $stmt->execute();

        // Get the last inserted PlayerID
        $playerId = $conn->lastInsertRowID();

        // Step 3: Insert into roster table (you can set a placeholder for LeagueMatchID if needed)
        $stmt = $conn->prepare("INSERT INTO roster (PlayerID, Position, ShirtNumber, TeamID, LeagueMatchID)
                                VALUES (:player_id, :position, :shirt_number, :team_id, :league_match_id)");

        $stmt->bindParam(':player_id', $playerId);
        $stmt->bindParam(':position', $_POST['position']);
        $stmt->bindParam(':shirt_number', $_POST['shirt_number']);
        $stmt->bindParam(':team_id', $_POST['team_id']);
        $stmt->bindValue(':league_match_id', 1, SQLITE3_INTEGER);  // Placeholder for LeagueMatchID (set to NULL or appropriate value)
        $stmt->execute();

        // Commit transaction
        $conn->exec("COMMIT");

        echo "Player added successfully!";
    } catch (Exception $e) {
        // If any error occurs, rollback the transaction
        $conn->exec("ROLLBACK");
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
    <>
        <!-- JAVA line for 'fontawesome' icons -->
        <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="../styles.css">


        <!-- Header -->
        <div class="header">
        <h2>Add Player</h2>
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
        

        <!-- Sidebar -->
        <div class="sidebar">
            <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">

            <!-- Creates buttons in the Sidebar -->
            <div class="sidebar-separator"></div>
            <div class="sidebar-button">
                <a href="AddUser.php">
                <i class="fa-solid fa-arrow-left"></i>Go Back
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
        <h1>Add New Player</h1>
        <form method="post" action="add_player.php">
            <label for="firstname">Firstname:</label>
            <input type="text" id="firstname" name="firstname" required>

            <label for="surname">Surname:</label>
            <input type="text" id="surname" name="surname" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" required>

            <label for="nationality">Nationality:</label>
            <input type="text" id="nationality" name="nationality" required>

            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="position">Position:</label>
            <select id="position" name="position">
                <option value="Goalkeeper">Goalkeeper</option>
                <option value="Defender">Defender</option>
                <option value="Midfielder">Midfielder</option>
                <option value="Forward">Forward</option>
            </select>

            <label for="shirt_number">Shirt Number:</label>
            <input type="number" id="shirt_number" name="shirt_number" min="1" max="99" required>

            <label for="team">Assign Team:</label>
            <select id="team" name="team_id" required>
                <?php
                $query = "SELECT TeamID, TeamName FROM team";
                $result = $conn->query($query);
                if ($result) {
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        echo "<option value='" . $row['TeamID'] . "'>" . $row['TeamName'] . "</option>";
                    }
                } else {
                    echo "Error: " . $conn->lastErrorMsg();
                }
                ?>
            </select>

            <button type="submit">Add Player</button>
        </form>
    </div>
</div>

        <div class="footer">
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>
       
</body>



        <!-- JAVA script to change colour of sidebar button referring to active page-->
        <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
        <script src="../sidebar.js"></script>
        <script>
            window.onload = preventPageRefresh;
        </script>
    </html>