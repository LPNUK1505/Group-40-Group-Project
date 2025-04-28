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
        $email = $row['Email'];
        $phoneNum = $row['PhoneNumber'];
        $dob = $row['DateOfBirth'];
        $nationality = $row['Nationality'];
        $role = $row['AccountType'];

    } else {
        die("User not found.");
    }
} else {
    die("Error fetching user data: " . $conn->lastErrorMsg());
}

// Handle form submission for updating the user's details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    // Sanitize and validate input data
    $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
    $surname = isset($_POST['surname']) ? $_POST['surname'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phoneNum = isset($_POST['phoneNum']) ? $_POST['phoneNum'] : '';
    $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
    $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : '';


    // Update the User table
    if ($userID) {
        $updateUserSql = "UPDATE User
                        SET Firstname = ?, Surname = ?, Email = ?, PhoneNumber = ?, DateOfBirth = ?, Nationality = ?
                        WHERE UserID = ?";
        $stmt = $conn->prepare($updateUserSql);
        $stmt->bindValue(1, $firstname, SQLITE3_TEXT);
        $stmt->bindValue(2, $surname, SQLITE3_TEXT);
        $stmt->bindValue(3, $email, SQLITE3_TEXT);
        $stmt->bindValue(4, $phoneNum, SQLITE3_TEXT);
        $stmt->bindValue(5, $dob, SQLITE3_TEXT);
        $stmt->bindValue(6, $nationality, SQLITE3_TEXT);
        $stmt->bindValue(7, $userID, SQLITE3_INTEGER);

        $stmt->execute();
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
            <a href="SettingsPersonal.php" class="stayOnPageLink">
                <i class="fa-solid fa-user"></i>Personal Details
            </a>
        </div>
        <div class="sidebar-button">
            <a href="SettingsSecurity.php">
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
                }

                .name_group {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    margin: 10px 33px;
                }
                .name_group i{
                    position: absolute;
                    left: 50px;
                    top: 55%;
                    font-size: 24px;
                    color: #022340;
                }

                .contact_group {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    margin: 10px 33px;
                }
                .contact_group i{
                    position: absolute;
                    left: 50px;
                    top: 55%;
                    font-size: 24px;
                    color: #022340;
                }

                .additional_group {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    margin: 10px 33px;
                }
                .additional_group i{
                    position: absolute;
                    left: 50px;
                    top: 50%;
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
            </style>

            <h1><b>Personal Details</b></h1>

            <h3>
                <div class="line_label">
                    <hr>
                    <span>Full Name</span>
                    <hr>
                </div>
                <div class="form_row">
                    <div class="name_group">
                        <label for="Firstname">*First Name</label>
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>
                    </div>
                    <div class="name_group">
                        <label for="Surname">*Surname</label>
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($surname); ?>" required>
                    </div>
                </div>

                <div class="line_label">
                    <hr>
                    <span>Contact</span>
                    <hr>
                </div>
                <div class="form_row">
                    <div class="contact_group">
                        <label for="email">*Email</label>
                        <i class="fa-solid fa-envelope"></i>
                        <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="contact_group">
                        <label for="phone-num">*Phone Number</label>
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" id="phoneNum" name="phoneNum" value="<?php echo htmlspecialchars($phoneNum); ?>" required>
                    </div>
                </div>

                <div class="line_label">
                    <hr>
                    <span>Additional</span>
                    <hr>
                </div>
                <div class="form_row">
                    <div class="additional_group">
                        <label for="dob">*Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>" required>
                    </div>
                    <div class="additional_group">
                        <label for="nationality">*Nationality</label>
                        <select id="nationality" name="nationality">
                        <option value="">Select Country</option>
                            <option value="<?php echo htmlspecialchars($nationality); ?>" selected>
                                <?php echo htmlspecialchars($nationality); ?>
                            </option>
                        </select>
                    </div>

                    <script>
                        // Fetch list of countries using Restcountries API
                        fetch('https://restcountries.com/v3.1/all')
                            .then(response => response.json())
                            .then(data => {
                                const nationalitySelect = document.getElementById('nationality');
                                // Sort countries alphabetically
                                data.sort((a, b) => a.name.common.localeCompare(b.name.common));

                                // Create option for each country
                                data.forEach(country => {
                                    const option = document.createElement('option');
                                    option.textContent = country.name.common;
                                    nationalitySelect.appendChild(option);
                                });
                            })
                            .catch(error => console.log('Error fetching countries:', error));
                    </script>
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