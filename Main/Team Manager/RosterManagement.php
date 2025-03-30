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
include_once __DIR__ . '/../Include/db.php';
echo "db.php included successfully!";

$sql = "SELECT
            User.UserID,
            User.Firstname,
            User.Surname,
            Player.PlayerID,
            Roster.Position,
            Roster.ShirtNumber
        FROM User
        JOIN Player ON User.UserID = Player.UserID
        JOIN Roster ON Player.PlayerID = Roster.PlayerID";

// Execute the query and check if it was successful
if ($result = $conn->query($sql)) {
    // Process the results if the query was successful
} else {
    die("Query failed: " . $conn->lastErrorMsg());
}
?>

<?php
$sql = "SELECT
            User.UserID,
            User.Firstname,
            User.Surname,
            Player.PlayerID,
            Roster.Position,
            Roster.ShirtNumber
        FROM User
        JOIN Player ON User.UserID = Player.UserID
        JOIN Roster ON Player.PlayerID = Roster.PlayerID";

$result = $conn->query($sql);  // Get the result of the query

// Check if the player ID is passed in the URL for deletion
if (isset($_GET['id'])) {
    $playerID = $_GET['id'];

    // SQL queries to delete from the Roster, Player, and User tables
    $deleteRosterSQL = "DELETE FROM Roster WHERE PlayerID = $playerID";
    $deletePlayerSQL = "DELETE FROM Player WHERE PlayerID = $playerID";
    $deleteUserSQL = "DELETE FROM User WHERE UserID = (SELECT UserID FROM Player WHERE PlayerID = $playerID)";

    // Start transaction to ensure all deletes are successful
    $conn->exec('BEGIN TRANSACTION');  // Start the transaction

    try {
        // Execute the DELETE queries directly
        $conn->exec($deleteRosterSQL);  // Delete from Roster table
        $conn->exec($deletePlayerSQL);  // Delete from Player table
        $conn->exec($deleteUserSQL);    // Delete from User table

        // Commit the transaction
        $conn->exec('COMMIT');  // Commit the transaction

        // Redirect to the Roster Management page after successful delete
        header('Location: RosterManagement.php');
        exit;

    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->exec('ROLLBACK');  // Rollback the transaction
        die("Error deleting player: " . $e->getMessage());
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

        <!-- Creates buttons in the Sidebar -->
        <div class="sidebar-separator"></div>
        <div class="sidebar-button">
            <a href="TeamOverview.html">
                <i class="fa-solid fa-people-group"></i>Team Overview
            </a>
        </div>
        <div class="sidebar-button">
            <a href="RosterManagement.html" class="stayOnPageLink">
                <i class="fa-solid fa-user-plus"></i>Roster Management
            </a>
        </div>
        <div class="sidebar-button">
            <a href="MatchPreperation.html">
                <i class="fa-solid fa-square-check"></i>Match Preperation
            </a>
        </div>
        <div class="sidebar-button">
            <a href="UpcomingMatches.html">
                <i class="fa-solid fa-calendar"></i>Upcoming Matches
            </a>
        </div>
        <div class="sidebar-button">
            <a href="PlayerStats.html">
                <i class="fa-solid fa-chart-simple"></i>Player Stats
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
        <h1> Roster Management </h1>
        <a href="add_player.php"><button>Add Player</button></a>

        <table border="1">
            <tr>
                <th>Firstname</th>
                <th>Surname</th>
                <th>Position</th>
                <th>Shirt Number</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Firstname']); ?></td>
                    <td><?php echo htmlspecialchars($row['Surname']); ?></td>
                    <td><?php echo htmlspecialchars($row['Position']); ?></td>
                    <td><?php echo htmlspecialchars($row['ShirtNumber']); ?></td>
                    <td>
                        <a href="edit_player.php?id=<?php echo $row['PlayerID']; ?>">Edit</a>
                        <a href="?id=<?php echo $row['PlayerID']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</html>