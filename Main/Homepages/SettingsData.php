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
        $password = $row['Password'];

    } else {
        die("User not found.");
    }
} else {
    die("Error fetching user data: " . $conn->lastErrorMsg());
}

// Handle downloading user data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['download'])) {
    $password1 = isset($_POST['password1']) ? $_POST['password1'] : '';
    $passwordconfirm1 = isset($_POST['passwordconfirm1']) ? $_POST['passwordconfirm1'] : '';

    // Fetch the User's data
    if ($userID) {
        if ($password1 === $passwordconfirm1 && $password1 === $password) {

            $fetchUserSql = "SELECT * FROM User WHERE UserID = ?";
            $stmt = $conn->prepare($fetchUserSql);
            $stmt->bindValue(1, $userID, SQLITE3_INTEGER);

            $result = $stmt->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);

            $columns = array_keys($user);
            $filename = "your_date_" . $user['UserID'] . ".csv";
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            foreach ($columns as $column) {
                fputcsv($output, array($column, $user[$column]));
            }
            fclose($output);

            exit;
        }
    }
}

// Handle deleting user data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $password2 = isset($_POST['password2']) ? $_POST['password2'] : '';
    $passwordconfirm2 = isset($_POST['passwordconfirm2']) ? $_POST['passwordconfirm2'] : '';

    // Delete the User's data
    if ($userID) {
        if ($password2 === $passwordconfirm2 && $password2 === $password) {

            $deleteUserSql = "DELETE FROM User WHERE UserID = ?";
            $stmt = $conn->prepare($deleteUserSql);
            $stmt->bindValue(1, $userID, SQLITE3_INTEGER);

            $stmt->execute();
        }
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
   <div class="sidebar">
        <img src="../GoIkonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">
        <!-- Creates buttons in the Sidebar -->
        <div class="sidebar-button">
            <a href="SettingsPersonal.html">
                <i class="fa-solid fa-user"></i>Personal Details
            </a>
        </div>
        <div class="sidebar-button">
            <a href="SettingsSecurity.html">
                <i class="fa-solid fa-unlock-keyhole"></i>Account Security
            </a>
        </div>
        <div class="sidebar-button">
            <a href="SettingsData.html" class="stayOnPageLink">
                <i class="fa-solid fa-cloud-arrow-up"></i>Manage Data
            </a>
        </div>
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
       <a href="ContactUs.html">Contact Us</a>
   </div>


   <!-- Creates Main Content area -->
   <div class="main-content">
        <form method="POST">
            <style>
                form{
                    text-align: center;
                }

                input {
                    padding: 10px 10px;
                    color: #333;
                    border: 2px solid #333;
                    border-radius: 50px;
                    margin: 10px 33px;
                    font-size: 16px;
                    font-family:Verdana, Tahoma, sans-serif;
                }

                label{
                    margin-left: 50px;
                }

                .message{
                    text-align: center;
                    font-size: 14px;
                    color: #f2f2f2;
                }

                .form_row {
                    display: flex;
                    width: auto;
                    margin: 0 300px;
                }

                .download_group {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    flex-direction: column;
                    flex: 1;
                    gap: 5px;
                }

                .password_group {
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    text-align: left;
                    font-size: 28px;
                    color: #f2f2f2;
                    margin: 10px 33px;
                }

                .delete_group {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    flex-direction: column;
                    flex: 1;
                    gap: 5px;
                }

                .line_label {
                    display: flex;
                    align-items: center;
                    margin: 10px 70px 0px 70px;
                }

                .line_label hr {
                    flex-grow: 1;
                    border: 0;
                    border-top: 2px solid #03588C;
                    margin: 0px;
                }

                .line_label span {
                    margin: 0px 10px;
                    font-weight: bold;
                    color: #03588C;
                }
            </style>

            <h1><b>Manage Data</b></h1>
            <p><b>Concerned about what data we store for you? Review your data, and correct, delete, or download it.</b></p>

            <h3>
                <div class="line_label">
                    <hr>
                    <span>Download Data</span>
                    <hr>
                </div>

                <div class="form_row">
                    <div class="password_group">
                        <label for="Password">*Password</label>
                        <input type="password" id="password1" name="password1">

                        <label for="PasswordConfirm">*Confirm Password</label>
                        <input type="password" id="passwordconfirm1" name="passwordconfirm1">
                    </div>

                    <div class="download_group">
                        <label class="message">This may take a few minutes.</label>
                        <button type="submit" name="download">Download</button>
                    </div>
                </div>

                <div class="line_label">
                    <hr>
                    <span>Delete Data</span>
                    <hr>
                </div>

                <div class="form_row">
                    <div class="password_group">
                        <label for="Password">*Password</label>
                        <input type="password" id="password2" name="password2">

                        <label for="PasswordConfirm">*Confirm Password</label>
                        <input type="password" id="passwordconfirm2" name="passwordconfirm2">
                    </div>

                    <div class="delete_group">
                        <label class="message">This action is irreversible.</label>
                        <button type="submit" name="delete">Delete</button>
                    </div>
                </div>
            </h3>
        </form>
    </div>

    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</html>