<?php
include_once __DIR__ . '/../Include/db.php'; // Path to db.php (which includes the Database class)

// Instantiate the Database class to get the connection
try {
    $dbInstance = new Database();  // Instantiate the Database class
    $conn = $dbInstance->getConnection(); // Get the connection
} catch (Exception $e) {
    die("Error: " . $e->getMessage());  // If there's an error, display a message and stop execution
}
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
<html>
   <!-- JAVA line for 'fontawesome' icons -->
   <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
   <link rel="stylesheet" href="../styles.css">


   <!-- Creates the Header at the top -->
   <div class="header">
        <div class="profile-box">
            <i class="fa fa-user"></i>
            <div>
                <div class="name">John Doe</div>
                <div class="role">Team Manager</div>
            </div>
            <i class="fa fa-chevron-down dropdown-icon"></i>
            <div class="dropdown">
                <a href="#">Profile</a>
                <a href="../Homepages/SettingsData.html">Manage Data</a>
                <a href="../Create Account and Login/Login.php">Sign Out</a>
            </div>
        </div>
   </div>
   

   <!-- Creates the Sidebar on the left hand side -->
   <div class="sidebar">
       <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">
       <div class="sidebar-separator"></div>

       <!-- Creates buttons in the Sidebar -->
       <div class="sidebar-button">
            <a onclick="location.href='RosterManagement.html'">
                <i class="fa-solid fa-backward"></i>Back
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
                    <i class="fa-solid fa-right-from-bracket"></i></i>Sign Out
                </a>
            </div>
        </div>
    </div>


    <!-- Creates the Footer at the bottom -->
    <div class="footer">

        <!-- Creates buttons in the footer -->
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>


   <!-- Creates Main Content area -->
   <div class="main-content">
       <h1> Add New Player </h1>
       <div class="form-container">
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
            $result = $conn->query($query);  // Use SQLite's query method instead of mysqli_query()

            // Check if the result is valid
            if ($result) {
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    // Output data from the row
                    echo "<option value='" . $row['TeamID'] . "'>" . $row['TeamName'] . "</option>";
                }
            } else {
                echo "Error: " . $conn->lastErrorMsg();  // SQLite error handling
            }
            ?>
            </select>

            <button type="submit">Add Player</button>
        </form>
    </div>
</html>