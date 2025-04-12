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
                <a href="../Homepages/SettingsData.html">Manage Data</a>
                <a href="../Create Account and Login/Login.php">Sign Out</a>
            </div>
        </div>
   </div>
   

   <!-- Creates the Sidebar on the left hand side -->
   <div class="sidebar" id="sidebar">
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
       <a href="AboutUs.html">About Us</a>
       <a href="ContactUs.html" class="stayOnPageLink">Contact Us</a>
   </div>


   <!-- Creates Main Content area -->
   <div class="main-content">
        <form action="https://api.web3forms.com/submit" method="POST">
            <style>
                form{
                    text-align: center;
                }

                input, textarea {
                    width: auto;
                    padding: 10px 10px 10px 45px;
                    color: #333;
                    border: 2px solid #333;
                    border-radius: 50px;
                    margin: 10px 33px;
                    font-size: 26px;
                    margin-bottom: 20px;
                    font-family:Verdana, Tahoma, sans-serif;
                    resize: none;
                }

                .name_fields {
                    display: flex;
                }

                .name_group {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    margin: 10px 33px;
                }

                label{
                    text-align: left;
                    margin-left: 50px;
                }

                .name_group i{
                    position: absolute;
                    left: 50px;
                    top: 55%;
                    font-size: 24px;
                    color: #022340;
                }

                .email_group {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    margin: 10px 33px;
                }

                .email_group i{
                    position: absolute;
                    left: 50px;
                    top: 55%;
                    font-size: 24px;
                    color: #022340;
                }

                .description_group {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    margin: 10px 33px;
                }

                .description_group i{
                    position: absolute;
                    left: 50px;
                    top: 55%;
                    font-size: 24px;
                    color: #022340;
                }

            </style>

            <h1><b>Contact Us</b></h1>
            <p><b>We'd love to hear from you! Contact us and we'll get back to you as soon as possible.</b></p>

            <h3>
            <input type="hidden" name="access_key" value="bd88788a-759d-4506-804a-71858374bd67">
                <div class="name_fields">
                    <div class="name_group">
                        <label for="Firstname">*First Name</label>
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="Firstname" name="Firstname" required>
                    </div>
                    <div class="name_group">
                        <label for="Surname">*Surname</label>
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="Surname" name="Surname" required>
                    </div>
                </div>

                <div class="email_group">
                    <label for="Email">*Email</label>
                    <i class="fa-solid fa-envelope"></i>
                    <input type="text" id="Email" name="Email">
                </div>

                <div class="description_group">
                    <label for="Description">What can we help you with?</label>
                    <i class="fa-solid fa-file-lines"></i>
                    <textarea name="Description" rows="4"></textarea>
                </div>

                <button type="submit">Send Email</button>
            </h3>
        </form>
   </div>

    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</html>