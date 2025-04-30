<?php
require_once '../Include/db.php';

$message = "";

// Only process the form if it was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username    = trim($_POST['username']);
    $password    = trim($_POST['password']);
    $firstname   = trim($_POST['firstname']);
    $lastname    = trim($_POST['lastname']);
    $email       = trim($_POST['email']);
    $role        = trim($_POST['role']);
    $nationality = trim($_POST['nationality']);
    $phoneNumber = trim($_POST['phonenumber'] ?? '');
    $dateOfBirth = trim($_POST['dateofbirth'] ?? '2000-01-01');
    $teamID      = $_POST['team_id'] ?? null;

    // Validate inputs
    $errors = [];
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($firstname)) $errors[] = "First name is required";
    if (empty($lastname)) $errors[] = "Last name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($nationality)) $errors[] = "Nationality is required";
    if (!preg_match('/^[\d\s\-+]+$/', $phoneNumber)) $errors[] = "Invalid phone number";
    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Get DB connection
            $dbInstance = new Database();
            $conn = $dbInstance->getConnection();

            // Begin transaction
            $conn->exec('BEGIN TRANSACTION');

            // Check if username or email already exists
            $checkStmt = $conn->prepare("SELECT UserID FROM User WHERE Username = ? OR Email = ?");
            $checkStmt->bindValue(1, $username, SQLITE3_TEXT);
            $checkStmt->bindValue(2, $email, SQLITE3_TEXT);
            $result = $checkStmt->execute();
            
            if ($result->fetchArray()) {
                throw new Exception("Username or email already exists");
            }

            // Prepare the insert statement for User table
            $stmt = $conn->prepare("
                INSERT INTO User 
                (Username, Password, Firstname, Surname, Email, AccountType, Nationality, PhoneNumber, DateOfBirth, RoleID)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Map role to RoleID and AccountType
            $roleIdMap = [
                'admin' => 1,
                'player' => 2,
                'team_manager' => 3,
                'referee' => 4
            ];
            
            $accountTypeMap = [
                'admin' => 'Admin',
                'player' => 'Player',
                'team_manager' => 'Team Manager',
                'referee' => 'Referee'
            ];
            
            $roleId = $roleIdMap[$role] ?? 2;
            $accountType = $accountTypeMap[$role];

            $stmt->bindValue(1, $username, SQLITE3_TEXT);
            $stmt->bindValue(2, $hashedPassword, SQLITE3_TEXT);
            $stmt->bindValue(3, $firstname, SQLITE3_TEXT);
            $stmt->bindValue(4, $lastname, SQLITE3_TEXT);
            $stmt->bindValue(5, $email, SQLITE3_TEXT);
            $stmt->bindValue(6, $accountType, SQLITE3_TEXT);
            $stmt->bindValue(7, $nationality, SQLITE3_TEXT);
            $stmt->bindValue(8, $phoneNumber, SQLITE3_TEXT);
            $stmt->bindValue(9, $dateOfBirth, SQLITE3_TEXT);
            $stmt->bindValue(10, $roleId, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                $newUserID = $conn->lastInsertRowID();
                
                // Handle role-specific assignments
                if ($role === 'player' && $teamID) {
                    $stmt = $conn->prepare("
                        INSERT INTO Player (UserID, TeamID, Goals, Assists, RedCards, YellowCards, Appearances)
                        VALUES (?, ?, 0, 0, 0, 0, 0)
                    ");
                    $stmt->bindValue(1, $newUserID, SQLITE3_INTEGER);
                    $stmt->bindValue(2, $teamID, SQLITE3_INTEGER);
                    $stmt->execute();
                } 
                elseif ($role === 'team_manager') {
                    // Insert into TeamManager table (team assignment is optional)
                    $stmt = $conn->prepare("
                        INSERT INTO TeamManager (UserID, StartDate)
                        VALUES (?, date('now'))
                    ");
                    $stmt->bindValue(1, $newUserID, SQLITE3_INTEGER);
                    $stmt->execute();
                    
                    // Only update team if one was selected
                    if ($teamID) {
                        $newManagerID = $conn->lastInsertRowID();
                        
                        // Check if team already has a manager
                        $checkManager = $conn->prepare("SELECT ManagerID FROM Team WHERE TeamID = ?");
                        $checkManager->bindValue(1, $teamID, SQLITE3_INTEGER);
                        $result = $checkManager->execute();
                        
                        if ($row = $result->fetchArray() && $row['ManagerID']) {
                            throw new Exception("This team already has a manager");
                        }
                        
                        // Update Team table with new manager
                        $stmt = $conn->prepare("
                            UPDATE Team SET ManagerID = ? WHERE TeamID = ?
                        ");
                        $stmt->bindValue(1, $newManagerID, SQLITE3_INTEGER);
                        $stmt->bindValue(2, $teamID, SQLITE3_INTEGER);
                        $stmt->execute();
                    }
                }
                
                // Commit transaction
                $conn->exec('COMMIT');
                
                // Close connection before redirecting
                $dbInstance->closeConnection();
                
                // Start session and set success message
                session_start();
                $_SESSION['account_created'] = true;
                
                // Redirect to login page
                header("Location: Login.php");
                exit();
            } else {
                throw new Exception("Failed to create account: " . $conn->lastErrorMsg());
            }

        } catch (Exception $e) {
            if (isset($conn)) $conn->exec('ROLLBACK');
            $message = $e->getMessage();
        }
    } else {
        $message = implode("<br>", $errors);
    }
}

// Fetch teams for dropdown
$teams = [];
try {
    $dbInstance = new Database();
    $conn = $dbInstance->getConnection();
    $result = $conn->query("SELECT TeamID, TeamName FROM Team ORDER BY TeamName");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $teams[] = $row;
    }
    $dbInstance->closeConnection();
} catch (Exception $e) {
    $message .= " Error loading teams: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Team Selection Specific Styling */
        #team-selection {
            margin-top: 15px;
            display: none;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        #team-selection select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            appearance: none;
        }

        /* Error Message Styling */
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="header"></div>
<div class="sidebar">
    <img src="../GoIkonLogoFinal.png" alt="Golkon Logo" class="goikon-logo">
    <div class="sidebar-separator"></div>
</div>
<div class="footer">
    <a href="AboutUs.html" class="stayOnPageLink">About Us</a>
    <a href="ContactUs.html">Contact Us</a>
</div>

<div class="main-content">
    <h1>Create Account</h1>
    <?php if (!empty($message)): ?>
        <div class="error-message"><?= $message ?></div>
    <?php endif; ?>
    
    <form method="post" id="create-account-form">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="firstname" placeholder="First Name" required>
        <input type="text" name="lastname" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="nationality" placeholder="Nationality" required>
        <input type="tel" name="phonenumber" placeholder="Phone Number" required>
        <input type="date" name="dateofbirth" placeholder="Date of Birth" required>
        
        <select name="role" id="role-select" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="player">Player</option>
            <option value="team_manager">Team Manager</option>
            <option value="referee">Referee</option>
        </select>
        
        <div id="team-selection">
            <select name="team_id" id="team_id">
                <option value="" disabled selected>Select Team</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= $team['TeamID'] ?>"><?= htmlspecialchars($team['TeamName']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit">Create Account</button>
    </form>
</div>

<script>
document.getElementById('role-select').addEventListener('change', function() {
    const teamSelection = document.getElementById('team-selection');
    const teamSelect = document.getElementById('team_id');
    
    if (this.value === 'player') {
        teamSelection.style.display = 'block';
        teamSelect.setAttribute('required', '');
    } else if (this.value === 'team_manager') {
        teamSelection.style.display = 'block';
        teamSelect.removeAttribute('required'); // Make optional for managers
    } else {
        teamSelection.style.display = 'none';
        teamSelect.removeAttribute('required');
    }
});
</script>

</body>
</html>