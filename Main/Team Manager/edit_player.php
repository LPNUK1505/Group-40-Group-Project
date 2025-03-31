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
// Get the PlayerID from the URL parameter
$playerId = isset($_GET['id']) ? $_GET['id'] : null;
if ($playerId === null) {
    die("Player ID is missing.");
}

// Initialize variables for the form
$firstname = '';
$surname = '';
$position = '';
$shirtNumber = '';

// Fetch the current player details from the database to prefill the form
$sql = "SELECT
            User.Firstname,
            User.Surname,
            Roster.Position,
            Roster.ShirtNumber
        FROM User
        JOIN Player ON User.UserID = Player.UserID
        JOIN Roster ON Player.PlayerID = Roster.PlayerID
        WHERE Player.PlayerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bindValue(1, $playerId, SQLITE3_INTEGER);  // Correct way to bind in SQLite
$result = $stmt->execute();

if ($result) {
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        $firstname = $row['Firstname'];
        $surname = $row['Surname'];
        $position = $row['Position'];
        $shirtNumber = $row['ShirtNumber'];
    } else {
        die("Player not found.");
    }
} else {
    die("Error fetching player data: " . $conn->lastErrorMsg());
}

// Handle form submission for updating the player
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input data
    $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
    $surname = isset($_POST['surname']) ? $_POST['surname'] : '';
    $position = isset($_POST['position']) ? $_POST['position'] : '';
    $shirtNumber = isset($_POST['shirt_number']) ? $_POST['shirt_number'] : '';

    // Update the User table
    $updateUserSql = "UPDATE User
                      SET Firstname = ?, Surname = ?
                      WHERE UserID = (SELECT UserID FROM Player WHERE PlayerID = ?)";
    $stmt = $conn->prepare($updateUserSql);
    $stmt->bindValue(1, $firstname, SQLITE3_TEXT);
    $stmt->bindValue(2, $surname, SQLITE3_TEXT);
    $stmt->bindValue(3, $playerId, SQLITE3_INTEGER);
    
    // Execute the update query for User
    if (!$stmt->execute()) {
        die("Error updating player details: " . $conn->lastErrorMsg());
    }

    // Update the Roster table
    $updateRosterSql = "UPDATE Roster
                        SET Position = ?, ShirtNumber = ?
                        WHERE PlayerID = ?";
    $stmt = $conn->prepare($updateRosterSql);
    $stmt->bindValue(1, $position, SQLITE3_TEXT);
    $stmt->bindValue(2, $shirtNumber, SQLITE3_INTEGER);
    $stmt->bindValue(3, $playerId, SQLITE3_INTEGER);

    // Execute the update query for Roster
    if ($stmt->execute()) {
        // Redirect back to the roster management page on success
        header('Location: RosterManagement.php');
        exit;
    } else {
        // Display error message if update fails
        echo "Error updating roster: " . $conn->lastErrorMsg();
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
   

   <!-- Creates the Sidebar on the left hand side -->
   <div class="sidebar">
       <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">
       <div class="sidebar-separator"></div>

       <!-- Creates buttons in the Sidebar -->
       <div class="sidebar-button">
            <a onclick="location.href='RosterManagement.php'">
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
    <div class ="main-content">
    <h1>Edit Player</h1>

    <!-- Edit Player Form -->
    <form method="post" action="edit_player.php?id=<?php echo $playerId; ?>">
           <label for="firstname">Firstname:</label>
           <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>

           <label for="surname">Surname:</label>
           <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($surname); ?>" required>

           <label for="position">Position:</label>
           <select id="position" name="position" required>
               <option value="Goalkeeper" <?php echo $position == 'Goalkeeper' ? 'selected' : ''; ?>>Goalkeeper</option>
               <option value="Defender" <?php echo $position == 'Defender' ? 'selected' : ''; ?>>Defender</option>
               <option value="Midfielder" <?php echo $position == 'Midfielder' ? 'selected' : ''; ?>>Midfielder</option>
               <option value="Forward" <?php echo $position == 'Forward' ? 'selected' : ''; ?>>Forward</option>
           </select>

           <label for="shirt_number">Shirt Number:</label>
           <input type="number" id="shirt_number" name="shirt_number" min="1" max="99" value="<?php echo htmlspecialchars($shirtNumber); ?>" required>

           <button type="submit">Update Player</button>
       </form>
    </div>
</html>