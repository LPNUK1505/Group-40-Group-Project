<?php
include_once __DIR__ . '/../Include/db.php'; // Path to db.php (which includes the Database class)

// Instantiate the Database class to get the connection
try {
    $dbInstance = new Database();  // Instantiate the Database class
    $conn = $dbInstance->getConnection(); // Get the connection
} catch (Exception $e) {
    die("Error: " . $e->getMessage());  // If there's an error, display a message and stop execution
}

// Initialize variables for the form
$userID = '8';

// Fetch the current user's details from the database to prefill the form
$sql = "SELECT *
        FROM User
        WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bindValue(1, $userID, SQLITE3_INTEGER);  // Correct way to bind in SQLite
$result = $stmt->execute();

if ($result) {
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        $firstname = $row['Firstname'];
        $surname = $row['Surname'];
        $role = $row['AccountType'];

    } else {
        die("User not found.");
    }
} else {
    die("Error fetching user data: " . $conn->lastErrorMsg());
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
                <div class="name"><?php echo $firstname . ' ' . $surname; ?></div>
                <div class="role"><?php echo $role; ?></div>
            </div>
            <i class="fa fa-chevron-down dropdown-icon"></i>
            <div class="dropdown">
                <a href="#">Profile</a>
                <a href="../Homepages/SettingsData.php">Manage Data</a>
                <a href="../Create Account and Login/Login.php">Sign Out</a>
            </div>
        </div>
   </div>
   

   <!-- Creates the Sidebar on the left hand side -->
   <div class="sidebar">
       <img src="../GoIkonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">

       <!-- Creates buttons in the Sidebar -->
       <div class="sidebar-separator"></div>
       <div class="sidebar-button">
            <a onclick="history.back()">
                <i class="fa-solid fa-backward"></i>Back
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
       <a href="AboutUs.php" class="stayOnPageLink">About Us</a>
       <a href="ContactUs.php">Contact Us</a>
   </div>


   <!-- Creates Main Content area -->
   <div class="main-content">
    <style>
        h1{
            text-align: center;
        }
        
        h3{
            text-align: center;
        }

        p{
            text-align: center;
        }
    </style>
       <h1><b>About Us</b></h1>
       <p><b >Some text about who we are and what we do.</b></p>

   </div>

    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</html>