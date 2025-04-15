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

        // Step 1: Insert new manager into user table
        $stmt = $conn->prepare("INSERT INTO user (Firstname, Surname, Password, Username, DateOfBirth, Nationality, PhoneNumber, Email, AccountType, RoleID)
                                VALUES (:firstname, :surname, :password, :username, :date_of_birth, :nationality, :phone_number, :email, 'Manager', 3)");

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

        // Step 2: Insert into teammanager table
        $stmt = $conn->prepare("INSERT INTO teammanager (UserID, StartDate, EndDate) 
                               VALUES (:user_id, :start_date, :end_date)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindValue(':start_date', date('Y-m-d')); // Current date as start date
        $stmt->bindValue(':end_date', null); // No end date initially
        $stmt->execute();

        // Get the last inserted ManagerID
        $managerId = $conn->lastInsertRowID();

        // Step 3: Insert manager certificates if any were selected
        if (!empty($_POST['certificates'])) {
            foreach ($_POST['certificates'] as $certificateId) {
                $stmt = $conn->prepare("INSERT INTO teammanager_certificate (ManagerID, CertificateID, IssueDate, ExpiryDate)
                                       VALUES (:manager_id, :certificate_id, :issue_date, :expiry_date)");
                $stmt->bindParam(':manager_id', $managerId);
                $stmt->bindParam(':certificate_id', $certificateId);
                $stmt->bindValue(':issue_date', date('Y-m-d')); // Current date as issue date
                
                // Calculate expiry date based on certificate's YearsActiveFor
                $certQuery = $conn->prepare("SELECT YearsActiveFor FROM certificate WHERE CertificateID = :cert_id");
                $certQuery->bindParam(':cert_id', $certificateId);
                $result = $certQuery->execute();
                $certData = $result->fetchArray(SQLITE3_ASSOC);
                
                $expiryDate = date('Y-m-d', strtotime('+' . $certData['YearsActiveFor'] . ' years'));
                $stmt->bindValue(':expiry_date', $expiryDate);
                $stmt->execute();
            }
        }

        // Commit transaction
        $conn->exec("COMMIT");

        echo "Manager added successfully!";
    } catch (Exception $e) {
        // If any error occurs, rollback the transaction
        $conn->exec("ROLLBACK");
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- JAVA line for 'fontawesome' icons -->
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <title>Add Manager</title>
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
        .certificate-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .certificate-option {
            display: flex;
            align-items: center;
        }
        .certificate-option input {
            width: auto;
            margin-right: 8px;
        }
        .certificate-description {
            font-size: 12px;
            color: #ccc;
            margin-left: 24px;
            margin-top: -10px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Creates the Header at the top -->
    <div class="header">
        <h2>Add Team Manager</h2>
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
    
    <!-- Creates the Sidebar on the left hand side -->
    <div class="sidebar">
        <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">

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

    <!-- Creates Main Content area -->
    <div class="player-content">
        <div class="player-form-wrapper">
            <h1>Add New Team Manager</h1>
            <form method="post" action="AddManager.php">
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

                <label>Certifications:</label>
                <div class="certificate-options">
                    <?php
                    // Fetch available certificates from database
                    $query = "SELECT CertificateID, Name, Issuer, YearsActiveFor, Notes FROM certificate";
                    $result = $conn->query($query);
                    if ($result) {
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            echo '<div class="certificate-option">';
                            echo '<input type="checkbox" id="cert_'.$row['CertificateID'].'" name="certificates[]" value="'.$row['CertificateID'].'">';
                            echo '<label for="cert_'.$row['CertificateID'].'">'.$row['Name'].' ('.$row['Issuer'].')</label>';
                            echo '<div class="certificate-description">Valid for '.$row['YearsActiveFor'].' year(s). '.$row['Notes'].'</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>

                <button type="submit">Add Manager</button>
            </form>
        </div>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>
   
    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</body>
</html>