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

// Handle form submission for updating the user's details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    // Sanitize and validate input data
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $passwordconfirm = isset($_POST['passwordconfirm']) ? $_POST['passwordconfirm'] : '';


    // Update the User table
    if ($userID) {
        if ($password === $passwordconfirm) {

            // Update password query
            $updatePasswordSql = "UPDATE User
                            SET Password = ?
                            WHERE UserID = ?";
            $stmt = $conn->prepare($updatePasswordSql);
            $stmt->bindParam(1, $password, SQLITE3_TEXT);
            $stmt->bindParam(2, $userID, SQLITE3_INTEGER);
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
                <a href="../Homepages/SettingsData.php">Manage Data</a>
                <a href="../Create Account and Login/Login.php">Sign Out</a>
            </div>
        </div>
   </div>
   

   <!-- Creates the Sidebar on the left hand side -->
   <div class="sidebar">
        <img src="../GoIkonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">
        <!-- Creates buttons in the Sidebar -->
        <div class="sidebar-button">
            <a href="SettingsPersonal.php">
                <i class="fa-solid fa-user"></i>Personal Details
            </a>
        </div>
        <div class="sidebar-button">
            <a href="SettingsSecurity.php" class="stayOnPageLink">
                <i class="fa-solid fa-unlock-keyhole"></i>Account Security
            </a>
        </div>
        <div class="sidebar-button">
            <a href="SettingsData.php">
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
       <a href="AboutUs.php">About Us</a>
       <a href="ContactUs.php">Contact Us</a>
   </div>


   <!-- Creates Main Content area -->
   <div class="main-content">
        <form method="POST">
            <style>
                form{
                    text-align: center;
                }

                input, select {
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
                
                input[type="date"]::-webkit-calendar-picker-indicator {
                    position: absolute;
                    left: 50px;
                    font-size: 24px;
                    color: #022340;
                }

                label{
                    text-align: left;
                    margin-left: 50px;
                }

                .form_row {
                    display: flex;
                    flex-direction: row;
                    flex: 1;
                }

                .password_group {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    margin: 10px 33px;
                }
                .password_group i{
                    position: absolute;
                    left: 50px;
                    top: 55%;
                    font-size: 24px;
                    color: #022340;
                }

                .TwoFA_group {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    align-items: start;
                    flex: 1;
                    margin: 10px 33px;
                }

                .TwoFA_group_group {
                    position: relative;
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    flex: 1;
                    gap: 15px;
                    width: auto;                    
                    padding: 10px 10px 10px 15px;
                    background-color: #f2f2f2;
                    border: 2px solid #333;
                    border-radius: 50px;
                    margin: 10px 33px;
                    font-size: 26px;
                    margin-bottom: 20px;
                }
                
                .TwoFA_group i{
                    font-size: 24px;
                    color: #022340;
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

                .switch {
                    position: relative;
                    width: 60px;
                    height: 34px;
                }

                .switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    -webkit-transition: .4s;
                    transition: .4s;
                }

                .slider:before {
                    position: absolute;
                    content: "";
                    height: 26px;
                    width: 26px;
                    left: 4px;
                    bottom: 4px;
                    background-color: white;
                    -webkit-transition: .4s;
                    transition: .4s;
                }

                input:checked + .slider {
                    background-color: #03588C;
                }

                input:focus + .slider {
                    box-shadow: 0 0 1px #03588C;
                }

                input:checked + .slider:before {
                    -webkit-transform: translateX(26px);
                    -ms-transform: translateX(26px);
                    transform: translateX(26px);
                }

                .slider.round {
                    border-radius: 34px;
                }

                .slider.round:before {
                    border-radius: 50%;
                }
            </style>

            <h1><b>Account Security</b></h1>

            <h3>
                <div class="line_label">
                    <hr>
                    <span>Password</span>
                    <hr>
                </div>
                <div class="form_row">
                    <div class="password_group">
                        <label for="Password">*Password</label>
                        <i class="fa-solid fa-key"></i>
                        <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>"required>
                    </div>
                    <div class="password_group">
                        <label for="PasswordConfirm">*Confirm Password</label>
                        <i class="fa-solid fa-key"></i>
                        <input type="password" id="passwordconfirm" name="passwordconfirm" required>
                    </div>
                </div>

                <button type="submit" name="save">Save Changes</button>
            </h3>
        </form>
    </div>

    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</html>