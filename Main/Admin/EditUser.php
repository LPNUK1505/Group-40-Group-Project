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

$userId = $roleId = $accountType = $firstname = $surname = $username = '';
$dateOfBirth = $nationality = $phoneNumber = $email = '';
$error = '';

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: Users.php");
    exit();
}

$userId = $_GET['id'];

// Fetch user data
$query = "SELECT UserID, RoleID, AccountType, Firstname, Surname, Username, 
          DateOfBirth, Nationality, PhoneNumber, Email 
          FROM User 
          WHERE UserID = :userId";

$stmt = $conn->prepare($query);
$stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if (!$row) {
    header("Location: Users.php?error=user_not_found");
    exit();
}

// Assign values from database
$roleId = $row['RoleID'];
$accountType = $row['AccountType'];
$firstname = $row['Firstname'];
$surname = $row['Surname'];
$username = $row['Username'];
$dateOfBirth = $row['DateOfBirth'];
$nationality = $row['Nationality'];
$phoneNumber = $row['PhoneNumber'];
$email = $row['Email'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $roleId = trim($_POST['role_id']);
    $accountType = trim($_POST['account_type']);
    $firstname = trim($_POST['firstname']);
    $surname = trim($_POST['surname']);
    $username = trim($_POST['username']);
    $dateOfBirth = trim($_POST['date_of_birth']);
    $nationality = trim($_POST['nationality']);
    $phoneNumber = trim($_POST['phone_number']);
    $email = trim($_POST['email']);

    
    if (empty($firstname) || empty($surname) || empty($username) || empty($email)) {
        $error = "Required fields are missing!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Update the user in the database
        $updateQuery = "UPDATE User SET 
                        RoleID = :role_id,
                        AccountType = :account_type,
                        Firstname = :firstname,
                        Surname = :surname,
                        Username = :username,
                        DateOfBirth = :date_of_birth,
                        Nationality = :nationality,
                        PhoneNumber = :phone_number,
                        Email = :email
                        WHERE UserID = :user_id";

        $stmt = $conn->prepare($updateQuery);
        $stmt->bindValue(':role_id', $roleId, SQLITE3_INTEGER);
        $stmt->bindValue(':account_type', $accountType, SQLITE3_TEXT);
        $stmt->bindValue(':firstname', $firstname, SQLITE3_TEXT);
        $stmt->bindValue(':surname', $surname, SQLITE3_TEXT);
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':date_of_birth', $dateOfBirth, SQLITE3_TEXT);
        $stmt->bindValue(':nationality', $nationality, SQLITE3_TEXT);
        $stmt->bindValue(':phone_number', $phoneNumber, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            header("Location: Users.php?updated=1");
            exit();
        } else {
            $error = "Error updating user: " . $conn->lastErrorMsg();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
    .user-content {
        position: relative;
        margin-left: 250px;
        padding: 40px 20px;
        min-height: calc(100vh - 160px);
        background-color: #022340;
        color: #F2F2F2;
        font-family: Verdana, Tahoma, sans-serif;
    }

    .user-form-wrapper {
        background: linear-gradient(145deg, #003354, #005680);
        max-width: 700px;
        margin: 40px auto;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        color: #ffffff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .user-form-wrapper h1 {
        text-align: center;
        font-size: 32px;
        margin-bottom: 30px;
        text-shadow: 1px 1px 3px #000;
    }

    .user-form-wrapper form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .user-form-wrapper label {
        font-size: 15px;
        font-weight: 600;
        color: #d2e9ff;
        margin-bottom: 5px;
        display: block;
    }

    .form-group {
        flex: 1 1 45%;
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        flex: 1 1 100%;
    }

    .user-form-wrapper input,
    .user-form-wrapper select {
        padding: 12px;
        border-radius: 8px;
        border: none;
        font-size: 15px;
        background-color: #e9f3fb;
        margin-bottom: 5px;
        transition: border 0.2s ease;
    }

    .user-form-wrapper input:focus,
    .user-form-wrapper select:focus {
        border: 2px solid #00c4ff;
        outline: none;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-save {
        background: linear-gradient(90deg, #00c4ff, #0059a8);
        color: white;
    }

    .btn-save:hover {
        background: linear-gradient(90deg, #009ecf, #004b91);
    }

    .btn-cancel {
        background: #95a5a6;
        color: white;
    }

    .btn-cancel:hover {
        background: #7f8c8d;
    }

    .error-message {
        color: #ff6b6b;
        background-color: rgba(255, 107, 107, 0.1);
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #ff6b6b;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .user-content {
            margin-left: 0;
            padding: 15px;
        }

        .user-form-wrapper {
            padding: 20px;
        }

        .form-group {
            flex: 1 1 100%;
        }

        .form-actions {
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            width: 100%;
        }
    }
</style>

</head>
<body>
    <div class="header">
        <h2>Edit User</h2>
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
        <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">
        <div class="sidebar-separator"></div>
        <div class="sidebar-button">
            <a href="Users.php">
                <i class="fa-solid fa-arrow-left"></i> Go Back
            </a>
        </div>
        <div class="sidebar-toolbox-container">
            <div class="sidebar-separator"></div>
            <div class="sidebar-toolbox-button">
                <a href="../Homepages/SettingsPersonal.php">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
            </div>
            <div class="sidebar-toolbox-button">
                <a href="../Create Account and Login/Login.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="user-content">
        <div class="user-form-wrapper">
            <h1>Edit User</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <label for="account_type">Account Type:</label>
<select id="account_type" name="account_type" required>
    <option value="Player" <?php echo $accountType == 'Player' ? 'selected' : ''; ?>>Player</option>
    <option value="Team Manager" <?php echo $accountType == 'Team Manager' ? 'selected' : ''; ?>>Team Manager</option>
    <option value="Referee" <?php echo $accountType == 'Referee' ? 'selected' : ''; ?>>Referee</option>
    <option value="Admin" <?php echo $accountType == 'Admin' ? 'selected' : ''; ?>>Admin</option>
</select>

                
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>
                
                <label for="surname">Surname:</label>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($surname); ?>" required>
                
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($dateOfBirth); ?>">
                
                <label for="nationality">Nationality:</label>
                <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars($nationality); ?>">
                
                <label for="phone_number">Phone Number:</label>
                <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phoneNumber); ?>">
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                
                <div class="form-actions">
                    <a href="Users.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>

    <script src="../sidebar.js"></script>
    <script>
        window.onload = function() {
            preventPageRefresh();
        };
        
        function preventPageRefresh() {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
                    e.preventDefault();
                }
            });
        }
    </script>
</body>
</html>